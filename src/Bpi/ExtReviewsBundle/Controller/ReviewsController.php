<?php

namespace Bpi\ExtReviewsBundle\Controller;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Entity\Audience;
use Bpi\ExtReviewsBundle\Document\Review as Review;
use Bpi\ExtReviewsBundle\Document\ReviewAudience;
use Bpi\ExtReviewsBundle\Document\ReviewBpiNode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;
use Guzzle;

class ReviewsController extends Controller
{
    /**
     * @param int $offset offset value that will be used in request.
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     *
     * Request service to get reviews and save them.
     */
    public function collectReviewsAction($offset)
    {
        $limit = 100;
        $requestLink = 'http://anbefalinger.deichman.no/api/reviews?offset=' . $offset . '&limit=' . $limit;
        $client = $this->get('bpi.extraviews.example.client');

        $request = $client->createRequest('GET', $requestLink);
        $response = $client->send($request);
        $body = $response->getBody();

        $data = json_decode($body);

        if (isset($data->error) && $data->error == 'no reviews found') {
            return $this->render('BpiExtReviewsBundle:Reviews:collectReviews.html.twig', array(
                'message' => 'All reviews was processed.',
            ));
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        $category = $this->get('doctrine_mongodb')->getRepository('BpiApiBundle:Entity\Category')->findOneBy(array('category' => 'Review'));

        if (empty($category)) {
            $category = new \Bpi\ApiBundle\Domain\Entity\Category();
            $category->setCategory('Review');
            $dm->persist($category);
            $dm->flush();
        }

        $mappedData = $this->mapReview($data->works, $category->getCategory());

        foreach ($mappedData as $review) {
            $reviewExist = $this->checkReviewExistence($review['review_uri']);
            if (is_object($reviewExist) && $reviewExist instanceof ReviewBpiNode) {
                $review['local_id'] = $reviewExist->getBpiId();
                $dm->remove($reviewExist);
                $dm->flush();
            }
            $document = $this->postNodeAction($review);
            $nodeId = $document->currentEntity()->property('id');

            $reviewBpiNode = new ReviewBpiNode();
            $reviewBpiNode->setBpiId($nodeId->getValue());
            $reviewBpiNode->setReviewUri($review['review_uri']);
            $dm->persist($reviewBpiNode);
            $dm->flush();
        }

        $offset += 100;
        return $this->redirect($this->generateUrl('bpi_ext_reviews_collect', array('offset' => $offset)));
    }

    /**
     * If no agency with such name don't exist else returns id of existing.
     * @param $agencyName string with name of agency which wrote review.
     * @return string public agency id.
     */
    private function prepareAgency($agencyName) {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $agency = $this->get('doctrine_mongodb')->getRepository('BpiApiBundle:Aggregate\Agency')->findOneBy(array('name' => $agencyName));
        if (empty($agency)) {
            $publicId = $this->preparePublicId();
            $agency = new Agency();
            $agency->setPublicId($publicId);
            $agency->setName($agencyName);
            $agency->setModerator('Automatic review');
            $agency->setPublicKey(null);
            $agency->setSecret(null);
            $dm->persist($agency);
            $dm->flush();
        }

        return (string) $agency->getPublicId();
    }

    /**
     * Determines id for new agency.
     * @return int|string public id for new agency.
     */
    private function preparePublicId() {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $agency = $dm->createQueryBuilder('BpiApiBundle:Aggregate\Agency')
            ->sort('public_id','desc')->limit(1)->getQuery()->getSingleResult();

        if (empty($agency)) {
            return '1';
        }

        $publicId = (int)$agency->getPublicId();
        return ++$publicId;
    }

    /**
     * @param $data raw data of fetched review.
     * @param string $category name of category for reviews.
     * @param string $agencyId id of agency for reviews.
     * @return array of mapped reviews.
     *
     * * Map raw data into review.
     */
    private function mapReview($data, $category)
    {
        $date = new \DateTime('now');
        $date = $date->format(\DateTime::W3C);
        $mappedData = array();
        foreach ($data as $key => $work) {
            foreach ($work->reviews as $k => $reviewItem) {
                $review['local_id'] = false;
                $review['audience'] = $this->prepareAudience($reviewItem->audience);
                $review['title'] = $reviewItem->title;
                $review['body'] = $reviewItem->text;
                $review['work'] = $reviewItem->work;
                $review['subject'] = $reviewItem->subject;
                $review['firstname'] = $reviewItem->reviewer->name;
                $review['creation'] = $date;
                $review['type'] = 'review';
                $review['category'] = $category;
                $review['agency_id'] = $this->prepareAgency($reviewItem->source->name);
                $review['lastname'] = '';
                $review['teaser'] = $this->truncateWordEnd(strip_tags($reviewItem->text), 200);
                $review['images'] = (isset($work->cover_url)) ? array(array('path' => $work->cover_url)) : null;
                $review['authorship'] = 1;
                $review['editable'] = 0;
                $review['review_uri'] = $reviewItem->uri;

                $mappedData[] = $review;
            }
        }

        return $mappedData;
    }

    /**
     * Prepare Audience for review.
     * @param array $audiences
     * @return mixed
     */
    private function prepareAudience($audiences)
    {
        if (count($audiences) > 1) {
            return $this->checkAudience('All');
        }
        elseif (count($audiences) == 1) {
            return $this->checkAudience($audiences[0]);
        }
    }

    /**
     * Check if audience exist if no create it.
     * @param string $audienceName
     * @return mixed
     */
    private function checkAudience($audienceName)
    {
        $audienceRepo = $this->get('doctrine_mongodb')->getRepository('BpiApiBundle:Entity\Audience');

        if ($audienceRepo->findOneBy(array('audience' => $audienceName))) {
            return $audienceName;
        }
        else {
            $audience = new \Bpi\ApiBundle\Domain\Entity\Audience();
            $audience->setAudience($audienceName);
            $audienceRepo->save($audience);
            return $audienceName;
        }
    }

    /**
     * Save review.
     * @param $data
     * @return \Bpi\RestMediaTypeBundle\Document
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \InvalidArgumentException
     */
    public function postNodeAction($data)
    {
        $assets = array();
        $service = $this->get('domain.push_service');

        $author = new \Bpi\ApiBundle\Domain\Entity\Author(
            new \Bpi\ApiBundle\Domain\ValueObject\AgencyId($data['agency_id']),
            null,
            $data['lastname'],
            $data['firstname']
        );

        $filesystem = $service->getFilesystem();

        $resource = new \Bpi\ApiBundle\Domain\Factory\ResourceBuilder($filesystem, $this->get('router'));
        $resource
            ->title($data['title'])
            ->body($data['body'])
            ->teaser($data['teaser'])
            ->setType($data['type'])
            ->ctime(\DateTime::createFromFormat(\DateTime::W3C, $data['creation']));

        // Download files and add them to resource
        $images = (!empty($data['images'])) ? $data['images'] : array();
        foreach ($images as $image) {
            $image = $image['path'];
            $ext = pathinfo(parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION);
            $filename = md5($image . microtime()); // . '.' . $ext;
            $file = $filesystem->createFile($filename);
            // @todo Download files in a proper way.
            $file->setContent(file_get_contents($image));
            $assets[] = array('file' => $file->getKey(), 'type' => 'attachment', 'extension' => $ext);
        }
        $resource->addAssets($assets);

        $profile = new \Bpi\ApiBundle\Domain\Entity\Profile();

        $params = new \Bpi\ApiBundle\Domain\Aggregate\Params();
        $params->add(
            new \Bpi\ApiBundle\Domain\ValueObject\Param\Authorship(
                $data['authorship']
            )
        );
        $params->add(
            new \Bpi\ApiBundle\Domain\ValueObject\Param\Editable(
                $data['editable']
            )
        );

        // Check for BPI ID
        $id = $data['local_id'];
        if ($id !== false) {
            $node = $this->get('doctrine.odm.mongodb.document_manager')->getRepository('BpiApiBundle:Aggregate\Node')->find($id);
            $dm = $this->get('doctrine_mongodb')->getManager();

            $dm->remove($node);
            $dm->flush();
        }

        $node = $this->get('domain.push_service')
            ->push($author, $resource, $data['category'], $data['audience'], $profile, $params);

        return $this->get("bpi.presentation.transformer")->transform($node);
    }

    /**
     * Check if review already exist and return it.
     * @param $reviewUri
     * @return bool|\Bpi\ExtReviewsBundle\Document\ReviewBpiNode
     */
    private function checkReviewExistence($reviewUri)
    {
        $reviewBpiNodeRepository = $this->get('doctrine_mongodb')->getRepository('BpiExtReviewsBundle:ReviewBpiNode');

        $selectParameters = array(
            'reviewUri' => $reviewUri,
        );

        $existingReview = $reviewBpiNodeRepository->findOneBy($selectParameters);
        if (!empty($existingReview)) {
            return $existingReview;
        }

        return false;
    }

    private function truncateWordEnd($string, $limit, $break=".", $pad="...") {
        // return with no change if string is shorter than $limit
        if (strlen($string) <= $limit) {
            return $string;
        }

        // is $break present between $limit and the end of the string?
        if (FALSE !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        }

        return $string;
    }
}

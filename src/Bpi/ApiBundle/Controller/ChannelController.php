<?php
/**
 * @file
 *  Channel controller class
 */

namespace Bpi\ApiBundle\Controller;

use Bpi\RestMediaTypeBundle\XmlResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\Rest\Util\Codes;

use Bpi\ApiBundle\Domain\Entity\Channel;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\RestMediaTypeBundle\Element\Facet;
use Bpi\RestMediaTypeBundle\Element\FacetTerm;

/**
 * Class ChannelController
 * @package Bpi\ApiBundle\Controller
 *
 * Rest controller for Channels
 */
class ChannelController extends BPIController
{
    /**
     * List all channels
     *
     * @Rest\Get("/")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\Channels
     */
    public function listChannelsAction()
    {
        $query = new \Bpi\ApiBundle\Domain\Entity\ChannelQuery();
        $query->amount(20);
        if (false !== ($amount = $this->getRequest()->query->get('amount', false))) {
            $query->amount($amount);
        }

        if (false !== ($offset = $this->getRequest()->query->get('offset', false))) {
            $query->offset($offset);
        }

        if (false !== ($search = $this->getRequest()->query->get('search', false))) {
            $query->search($search);
        }

        $filters = array();
        $logicalOperator = '';
        if (false !== ($filter = $this->getRequest()->query->get('filter', false))) {
            foreach ($filter as $field => $value) {
                if ($field == 'agency_id' && is_array($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $filters['agency_id'][] = $val;
                    }
                }
            }
            if (isset($filter['logicalOperator']) && !empty($filter['logicalOperator'])) {
                $logicalOperator = $filter['logicalOperator'];
            }
        }

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\ChannelFacet');
        $facets = $facetRepository->getFacetsByRequest($filters, $logicalOperator);
        $query->filter($facets->channelIds);

        if (false !== ($sort = $this->getRequest()->query->get('sort', false))) {
            foreach ($sort as $field => $order) {
                $query->sort($field, $order);
            }
        }

        $channels = $this->getRepository('BpiApiBundle:Entity\Channel')->findByQuery($query);

        if (null === $channels) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, 'No channels found.');
        }

        $response = $this->get('bpi.presentation.channels');
        $response->setTotal($query->total);
        $response->setOffset($query->offset);
        $response->setAmount($query->amount);

        foreach ($facets->facets as $name => $facet) {
            $theFacet = new Facet(Facet::TYPE_STRING, $name);
            foreach ($facet as $key => $term) {
                $value = $term;
                $title = null;
                if ($name == 'agency_id') {
                    $value = $term['count'];
                    $title = $term['agencyName'];
                } elseif (isset($term['count'])) {
                    $value = $term['count'];
                    $title = isset($term['title']) ? $term['title'] : null;
                }

                $term = new FacetTerm($key, $value, $title);
                $theFacet->addTerm($term);
            }

            $response->addFacet($theFacet);
        }

        foreach ($channels as $channel) {
            $response->addChannel($channel);
        }

        return $response;
    }

    /**
     * Get channel description for specific channel by it's id.
     *
     * @Rest\Get("/{channelId}")
     * @Rest\View("")
     *
     * @param string $channelId.
     *
     * @return \Bpi\RestMediaTypeBundle\Channels
     */
    public function getChannelInfoAction($channelId) {
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');

        $channel = $channelRepository->findOneBy(
            array(
                'id' => $channelId,
                'channelDeleted' => false,
            )
        );

        if (null === $channel) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, 'Channel with id ' . $channelId . ' not found.');
        }

        $response = $this->get('bpi.presentation.channels');
        $response->addChannel($channel);

        return $response;
    }

    /**
     * List channels of given user
     *
     * @param $userId
     *
     * @Rest\Get("/user/{userId}")
     * @Rest\View()
     *
     * @return Document $document
     */
    public function listUsersChannelsAction($userId)
    {
        if (!isset($userId) || empty($userId)) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST, 'User external id required for listing channels.');
        }

        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');

        $userAgency = $this->getAgencyFromHeader();
        $userAgencyId = $userAgency->getAgencyId()->id();

        if (null === $userAgency) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, 'Agency with external id ' . $userId . ' not found.');
        }

        $user = $userRepository->findOneBy(
            array(
                'id' => $userId,
                'userAgency.$id' => new \MongoId($userAgency->getId())
            )
        );

        if (null === $user) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, 'User with given externalId: ' . $userId . ' and agency public_id: ' . $userAgencyId . ' not found.');
        }

        $xml = $this->get('bpi.presentation.channels');
        $channels = $channelRepository->findChannelsByUser($user);
        if(!empty($channels)) {
            foreach ($channels as $channel) {
                $xml->addChannel($channel);
            }
        }

        return $xml;
    }

    /**
     * Create new channel
     *
     * @Rest\Post("/")
     * @Rest\View(statusCode="201")
     */
    public function createChannelAction()
    {
        $dm = $this->getDoctrineManager();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'name' => 0,
            'editorId' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if ($count  == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        $user = $userRepository->findOneById($params['editorId']);
        if ($user === null) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "User with id = '{$params['editorId']}' not found.");
        }

        $channel = new Channel();
        $channel->setChannelName($params['name']);
        $channel->setChannelAdmin($user);

        if (isset($params['channelDescription']) && !empty($params['channelDescription'])) {
            $channel->setChannelDescription($params['channelDescription']);
        }

        $dm->persist($channel);
        $dm->flush();

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\ChannelFacet');
        $facetRepository->prepareFacet($channel);

        $response = $this->get('bpi.presentation.channels');
        $response->addChannel($channel);

        return $response;
    }

    /**
     * @param string $channelId.
     *
     * @Rest\Post("/edit/{channelId}")
     * @Rest\View()
     */
    public function editChannelAction($channelId) {
        $dm = $this->getDoctrineManager();
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'channelName' => 0,
            'channelDescription' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if ($count  == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        $channel = $channelRepository->find($channelId);
        if (null === $channel) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "Channel with id = '{$channelId}' not found.");
        }

        $channel->setChannelName($params['channelName']);
        $channel->setChannelDescription($params['channelDescription']);

        $dm->persist($channel);
        $dm->flush();

        $response = $this->get('bpi.presentation.channels');
        $response->addChannel($channel);

        return $response;
    }

    /**
     * Add editors to channels
     *
     * @Rest\Post("/add/editor")
     * @Rest\View()
     */
    public function addEditorToChannelAction()
    {
        $dm = $this->getDoctrineManager();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'channelId' => 0,
            'adminId' => 0,
            'editorId' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if ($count  == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        // Check channel exist, load it.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "Channel with id = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        if ($admin->getId() != $params['adminId']) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, "User with id  = '{$params['adminId']}' can't add users to channel.");
        }

        $skipped = array();
        $editors = $channel->getChannelEditors();
        $success = array();
        foreach ($params['users'] as $user) {
            $u = $userRepository->findOneById($user['editorId']);

            if ($u === null || $editors->contains($u) || $admin == $user) {
                $skipped[] = $user['editorId'];
                continue;
            }

            $channel->addChannelEditor($u);
            $success[] = $user['editorId'];
        }

        $dm->persist($channel);
        $dm->flush();

        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);
        return $xml;
    }

    /**
     * Remove user from channel
     *
     * @Rest\Post("/remove/editor")
     * @Rest\View()
     *
     * @return Response
     */
    public function removeEditorFromChannelAction()
    {
        $dm = $this->getDoctrineManager();
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'channelId' => 0,
            'adminId' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if ($count  == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        // Check channel exist, load it.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "Channel with id = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        if ($admin->getId() != $params['adminId']) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "User with id  = '{$params['adminId']}' can't remove users from channel.");
        }

        $skipped = array();
        $success = array();
        $editors = $channel->getChannelEditors();
        foreach ($params['users'] as $user) {
            $u = $userRepository->findOneById($user['editorId']);

            if ($u === null || !$editors->contains($u) || $admin == $user) {
                $skipped[] = $user['editorId'];
                continue;
            }
            $channel->removeChannelEditor($u);
            $success[] = $user['editorId'];
        }

        $dm->persist($channel);
        $dm->flush();

        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);
        return $xml;
    }

    /**
     * Add node to channel.
     *
     * @Rest\Post("/add/node")
     * @Rest\View()
     *
     * @return Response
     */
    public function addNodeToChannelAction()
    {
        $dm = $this->getDoctrineManager();
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $nodeRepository = $this->getRepository('BpiApiBundle:Aggregate\Node');

        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'editorId' => 0,
            'channelId' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if ($count  == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        // Try to load channel.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "Channel with id  = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        $editors = $channel->getChannelEditors();
        $is_editor = false;
        foreach ($editors as $editor) {
            if($editor->getId() == $params['editorId']) {
                $is_editor = true;
                break;
            }
        }
        if ($admin->getId() != $params['editorId'] && !$is_editor) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, "User with id  = '{$params['editorId']}' can't push to this channel.");
        }

        $skipped = array();
        $success = array();
        foreach ($params['nodes'] as $data) {
            // Check node exist, load it.
            $node = $nodeRepository->findOneById($data['nodeId']);
            if ($node === null) {
                $skipped[] = $data['nodeId'];
                continue;
            }

            $nodes = $channel->getChannelNodes();
            if ($nodes->contains($node)) {
                $skipped[] = $node->getId();
                continue;
            }

            // Try to add node.
            try {
                $channel->addChannelNode($node);
                $success[] = $node->getId();
            } catch (\Exception $e) {
                throw new HttpException(Codes::HTTP_INTERNAL_SERVER_ERROR, "Internal error on adding node.");
            }
        }

        $dm->persist($channel);
        $dm->flush();

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');
        $facetRepository->addChannelToFacet($channel->getId(), $params['nodes']);

        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);

        return $xml;
    }

    /**
     * Remove node from channel.
     *
     * @Rest\Post("/remove/node")
     * @Rest\View()
     *
     * @return Response
     */
    public function removeNodeFromChannelAction()
    {
        $dm = $this->getDoctrineManager();
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $nodeRepository = $this->getRepository('BpiApiBundle:Aggregate\Node');

        $params = $this->getAllRequestParameters();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = array(
            'editorId' => 0,
            'channelId' => 0,
        );
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if ($count  == 0) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST, "Param '{$param}' is required.");
            }
        }

        // Try to load channel.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "Channel with id  = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        $editors = $channel->getChannelEditors();
        $is_editor = false;
        foreach ($editors as $editor) {
            if($editor->getId() == $params['editorId']) {
                $is_editor = true;
                break;
            }
        }
        if ($admin->getId() != $params['editorId'] && !$is_editor) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, "User with id  = '{$params['editorId']}' can't push to this channel.");
        }

        $success = array();
        $skipped = array();
        foreach ($params['nodes'] as $data) {
            // Check node exist, load it.
            $node = $nodeRepository->findOneById($data['nodeId']);
            if ($node === null) {
                $skipped[] = $data['nodeId'];
                continue;
            }

            $nodes = $channel->getChannelNodes();
            if (!$nodes->contains($node)) {
                $skipped[] = $node->getId();
                continue;
            }

            // Try to add node.
            try {
                $channel->removeChannelNode($node);
                $success[] = $node->getId();
            } catch (\Exception $e) {
                return new Response('Internal error on removing node.', 500);
            }
        }

        $dm->persist($channel);
        $dm->flush();

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');
        $facetRepository->removeChannelFromFacet($channel->getId(), $params['nodes']);

        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);

        return $xml;
    }

    /**
     * Remove channel bu Id.
     *
     * @param string $channelId
     *
     * @Rest\Delete("/remove/{channelId}")
     * @Rest\View()
     *
     * @return XmlGroupOperation.
     */
    public function removeChannelAction($channelId) {
        $dm = $this->getDoctrineManager();
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $channel = $channelRepository->find($channelId);

        if (null === $channel) {
            throw new HttpException(Codes::HTTP_NOT_FOUND, "Channel with id  = '{$channelId}' not found.");
        }

        $channel->setChannelDeleted(true);
        $dm->persist($channel);
        $dm->flush();

        $xml = new XmlResponse();
        $xml->setCode(200);
        $xml->setMessage("Channel with Id " . $channelId . " removed.");

        return $xml;
    }
}

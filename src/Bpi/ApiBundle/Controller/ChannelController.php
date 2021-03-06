<?php

namespace Bpi\ApiBundle\Controller;

use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Entity\ChannelFacet;
use Bpi\ApiBundle\Domain\Entity\User;
use Bpi\ApiBundle\Domain\Repository\FacetRepository;
use Bpi\RestMediaTypeBundle\XmlResponse;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FOS\RestBundle\Controller\Annotations as Rest;

use Bpi\ApiBundle\Domain\Entity\Channel;
use Bpi\RestMediaTypeBundle\Element\Facet;
use Bpi\RestMediaTypeBundle\Element\FacetTerm;

/**
 * Class ChannelController.
 */
class ChannelController extends FOSRestController
{
    use BpiRequestParamSanitizerTrait;

    const CHANNEL_LIST_COUNT = 10;

    /**
     * Handles channel listing.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Get("/channel")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function listChannelsAction(Request $request)
    {
        $query = new \Bpi\ApiBundle\Domain\Entity\ChannelQuery();

        $query->amount($request->query->get('amount', self::CHANNEL_LIST_COUNT));
        $query->offset($request->query->get('offset', 0));

        if ($search = $request->query->get('search')) {
            $query->search($search);
        }

        $filters = [];
        $logicalOperator = '';
        if ($filter = $request->query->get('filter', [])) {
            foreach ($filter as $field => $value) {
                if ($field == 'agency_id' && is_array($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $filters['agency_id'][] = $val;
                    }
                } elseif ($field == 'id' && is_array($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $filters['id'][] = $val;
                    }
                }
            }
            if (isset($filter['logicalOperator']) && !empty($filter['logicalOperator'])) {
                $logicalOperator = $filter['logicalOperator'];
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Repository\ChannelFacetRepository $channelFacetRepository */
        $channelFacetRepository = $this->get('doctrine_mongodb')->getRepository(ChannelFacet::class);
        $facets = $channelFacetRepository->getFacetsByRequest($filters, $logicalOperator);
        $query->filter($facets->channelIds);

        if ($sort = $request->query->get('sort', [])) {
            foreach ($sort as $field => $order) {
                $query->sort($field, $order);
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Repository\ChannelRepository $channelRepository */
        $channelRepository = $this->get('doctrine_mongodb')->getRepository(Channel::class);
        $channels = $channelRepository->findByQuery($query);

        if (null === $channels) {
            throw new NotFoundHttpException('No channels found.');
        }

        /** @var \Bpi\RestMediaTypeBundle\Channels $response */
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
     * Handles channel info fetch.
     *
     * @Rest\Get("/channel/{id}")
     * @Rest\View()
     *
     * @param \Bpi\ApiBundle\Domain\Entity\Channel $channel Loaded channel entity.
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function getChannelInfoAction(Channel $channel)
    {
        /** @var \Bpi\RestMediaTypeBundle\Channels $response */
        $response = $this->get('bpi.presentation.channels');

        $response->addChannel($channel);

        return $response;
    }

    /**
     * Handles listing of channel for a given user.
     *
     * @param \Bpi\ApiBundle\Domain\Entity\User $user Loaded user entity.
     *
     * @Rest\Get("/channel/user/{id}")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function listUsersChannelsAction(User $user)
    {
        /** @var \Bpi\ApiBundle\Domain\Repository\ChannelRepository $channelRepository */
        $channelRepository = $this->get('doctrine_mongodb')->getRepository(Channel::class);

        /** @var \Bpi\RestMediaTypeBundle\Channels $xml */
        $xml = $this->get('bpi.presentation.channels');
        $channels = $channelRepository->findChannelsByUser($user);
        if (!empty($channels)) {
            foreach ($channels as $channel) {
                $xml->addChannel($channel);
            }
        }

        return $xml;
    }

    /**
     * Handles new channel creation.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Post("/channel")
     * @Rest\View(statusCode="201")
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function createChannelAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /** @var \Bpi\ApiBundle\Domain\Repository\UserRepository $userRepository */
        $userRepository = $dm->getRepository(User::class);
        $params = $request->request->all();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = [
            'name' => 0,
            'editorId' => 0,
        ];
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Entity\User $user */
        $user = $userRepository->find($params['editorId']);
        if (!$user) {
            throw new NotFoundHttpException("User with id = '{$params['editorId']}' not found.");
        }

        $channel = new Channel();
        $channel->setChannelName($params['name']);
        $channel->setChannelAdmin($user);

        if (isset($params['channelDescription']) && !empty($params['channelDescription'])) {
            $channel->setChannelDescription($params['channelDescription']);
        }

        $dm->persist($channel);
        $dm->flush();

        /** @var \Bpi\ApiBundle\Domain\Repository\ChannelFacetRepository $facetRepository */
        $facetRepository = $dm->getRepository(ChannelFacet::class);
        $facetRepository->prepareFacet($channel);

        /** @var \Bpi\RestMediaTypeBundle\Channels $response */
        $response = $this->get('bpi.presentation.channels');
        $response->addChannel($channel);

        return $response;
    }

    /**
     * Handles channel edit action.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     * @param \Bpi\ApiBundle\Domain\Entity\Channel $channel Loaded channel entity.
     *
     * @Rest\Post("/channel/edit/{id}")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function editChannelAction(Request $request, Channel $channel)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $params = $request->request->all();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = [
            'channelName' => 0,
            'channelDescription' => 0,
        ];
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        $channel->setChannelName($params['channelName']);
        $channel->setChannelDescription($params['channelDescription']);

        $dm->persist($channel);
        $dm->flush();

        /** @var \Bpi\RestMediaTypeBundle\Channels $response */
        $response = $this->get('bpi.presentation.channels');
        $response->addChannel($channel);

        return $response;
    }

    /**
     * Handles editors addition to a certain channel.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Post("/channel/add/editor")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function addEditorToChannelAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $userRepository = $dm->getRepository(User::class);
        $channelRepository = $dm->getRepository(Channel::class);
        $params = $request->request->all();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = [
            'channelId' => 0,
            'adminId' => 0,
            'editorId' => 0,
        ];
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        /** @var \Bpi\ApiBundle\Domain\Entity\Channel $channel */
        $channel = $channelRepository->find($params['channelId']);
        if (!$channel) {
            throw new NotFoundHttpException("Channel with id = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        if ($admin->getId() != $params['adminId']) {
            throw new HttpException(404, "User with id  = '{$params['adminId']}' can't add users to channel.");
        }

        $skipped = [];
        $editors = $channel->getChannelEditors();
        $success = [];
        foreach ($params['users'] as $user) {
            $u = $userRepository->find($user['editorId']);

            if ($u === null || $editors->contains($u) || $admin == $user) {
                $skipped[] = $user['editorId'];
                continue;
            }

            $channel->addChannelEditor($u);
            $success[] = $user['editorId'];
        }

        $dm->persist($channel);
        $dm->flush();

        /** @var \Bpi\RestMediaTypeBundle\XmlGroupOperation $xml */
        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);

        return $xml;
    }

    /**
     * Handles editor removal from a certain channel.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Post("/channel/remove/editor")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function removeEditorFromChannelAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('dcotrine_mongodb')->getManager();
        /** @var \Bpi\ApiBundle\Domain\Repository\UserRepository $userRepository */
        $userRepository = $dm->getRepository(User::class);
        /** @var \Bpi\ApiBundle\Domain\Repository\ChannelRepository $channelRepository */
        $channelRepository = $dm->getRepository(Channel::class);
        $params = $request->request->all();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = [
            'channelId' => 0,
            'adminId' => 0,
        ];
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        /** @var Channel $channel */
        $channel = $channelRepository->find($params['channelId']);
        if (!$channel) {
            throw new NotFoundHttpException("Channel with id = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        if ($admin->getId() != $params['adminId']) {
            throw new NotFoundHttpException("User with id  = '{$params['adminId']}' can't remove users from channel.");
        }

        $skipped = [];
        $success = [];
        $editors = $channel->getChannelEditors();
        foreach ($params['users'] as $user) {
            $u = $userRepository->find($user['editorId']);

            if ($u === null || !$editors->contains($u) || $admin == $user) {
                $skipped[] = $user['editorId'];
                continue;
            }
            $channel->removeChannelEditor($u);
            $success[] = $user['editorId'];
        }

        $dm->persist($channel);
        $dm->flush();

        /** @var \Bpi\RestMediaTypeBundle\XmlGroupOperation $xml */
        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);

        return $xml;
    }

    /**
     * Handles nodes addition to a certain channel.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Post("/channel/add/node")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function addNodeToChannelAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /** @var \Bpi\ApiBundle\Domain\Repository\ChannelRepository $channelRepository */
        $channelRepository = $dm->getRepository(Channel::class);
        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $nodeRepository */
        $nodeRepository = $dm->getRepository(Node::class);

        $params = $request->request->all();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = [
            'editorId' => 0,
            'channelId' => 0,
        ];
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        /** @var Channel $channel */
        $channel = $channelRepository->find($params['channelId']);
        if ($channel === null) {
            throw new NotFoundHttpException("Channel with id  = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        $editors = $channel->getChannelEditors();
        $is_editor = false;
        foreach ($editors as $editor) {
            if ($editor->getId() == $params['editorId']) {
                $is_editor = true;
                break;
            }
        }
        if ($admin->getId() != $params['editorId'] && !$is_editor) {
            throw new AccessDeniedHttpException("User with id  = '{$params['editorId']}' can't push to this channel.");
        }

        $skipped = [];
        $success = [];
        foreach ($params['nodes'] as $data) {
            // Check node exist, load it.
            $node = $nodeRepository->find($data['nodeId']);
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
                throw new HttpException(500, "Internal error on adding node.");
            }
        }

        $dm->persist($channel);
        $dm->flush();

        /** @var \Bpi\ApiBundle\Domain\Repository\FacetRepository $facetRepository */
        $facetRepository = $dm->getRepository(\Bpi\ApiBundle\Domain\Entity\Facet::class);
        $facetRepository->addChannelToFacet($channel->getId(), $params['nodes']);

        /** @var \Bpi\RestMediaTypeBundle\XmlGroupOperation $xml */
        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);

        return $xml;
    }

    /**
     * Handles node removal from a certain channel.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Post("/channel/remove/node")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function removeNodeFromChannelAction(Request $request)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        /** @var \Bpi\ApiBundle\Domain\Repository\ChannelRepository $channelRepository */
        $channelRepository = $dm->getRepository(Channel::class);
        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $nodeRepository */
        $nodeRepository = $dm->getRepository(Node::class);

        $params = $request->request->all();
        // Strip all params.
        $this->stripParams($params);

        $requiredParams = [
            'editorId' => 0,
            'channelId' => 0,
        ];
        $this->checkParams($params, $requiredParams);

        foreach ($requiredParams as $param => $count) {
            if (!$count) {
                throw new BadRequestHttpException("Param '{$param}' is required.");
            }
        }

        // Try to load channel.
        /** @var \Bpi\ApiBundle\Domain\Entity\Channel $channel */
        $channel = $channelRepository->find($params['channelId']);
        if (!$channel) {
            throw new NotFoundHttpException("Channel with id  = '{$params['channelId']}' not found.");
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        $editors = $channel->getChannelEditors();
        $is_editor = false;
        foreach ($editors as $editor) {
            if ($editor->getId() == $params['editorId']) {
                $is_editor = true;
                break;
            }
        }
        if ($admin->getId() != $params['editorId'] && !$is_editor) {
            throw new HttpException(403, "User with id  = '{$params['editorId']}' can't push to this channel.");
        }

        $success = [];
        $skipped = [];
        foreach ($params['nodes'] as $data) {
            // Check node exist, load it.
            /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $node */
            $node = $nodeRepository->find($data['nodeId']);
            if (!$node) {
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
                throw new HttpException(500, 'Internal error on removing node.');
            }
        }

        $dm->persist($channel);
        $dm->flush();

        /** @var FacetRepository $facetRepository */
        $facetRepository = $dm->getRepository(Facet::class);
        $facetRepository->removeChannelFromFacet($channel->getId(), $params['nodes']);

        /** @var \Bpi\RestMediaTypeBundle\XmlGroupOperation $xml */
        $xml = $this->get('bpi.presentation.xmlgroupoperation');
        $xml->setCode(200);
        $xml->setSkipped(count($skipped));
        $xml->setSkippedList($skipped);
        $xml->setSuccess(count($success));
        $xml->setSuccessList($success);

        return $xml;
    }

    /**
     * Handles channel removal.
     *
     * @param \Bpi\ApiBundle\Domain\Entity\Channel $channel Loaded channel entity.
     *
     * @Rest\Delete("/channel/remove/{id}")
     * @Rest\View()
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse.
     */
    public function removeChannelAction(Channel $channel)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $channel->setChannelDeleted(true);
        $dm->persist($channel);
        $dm->flush();

        $xml = new XmlResponse();
        $xml->setCode(200);
        $xml->setMessage("Channel with Id {$channel->getId()} removed.");

        return $xml;
    }
}

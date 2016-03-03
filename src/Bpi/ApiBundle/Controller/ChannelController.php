<?php
/**
 * @file
 *  Channel controller class
 */

namespace Bpi\ApiBundle\Controller;

use Bpi\RestMediaTypeBundle\XmlResponse;
use Bpi\RestMediaTypeBundle\XmlError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints;

use FOS\RestBundle\Controller\Annotations as Rest;

use Bpi\ApiBundle\Domain\Entity\Channel;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\History;

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
     * @return \Bpi\RestMediaTypeBundle\Channels | XmlError
     */
    public function listChannelsAction()
    {
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');

        $allChannels = $channelRepository->findBy(array('channelDeleted' => false));

        // Check if not deleted channels exist.
        if(null === $allChannels) {
            $xmlError = $this->get('bpi.presentation.xmlerror');
            $xmlError->setCode(404);
            $xmlError->setError('No channels found.');
            return $xmlError;
        }

        // Prepare existing channels for response.
        $xml = $this->get('bpi.presentation.channels');
        foreach ($allChannels as $channel) {
            $xml->addChannel($channel);
        }

        return $xml;
    }

    /**
     * Get channel description for specific channel by it's id.
     *
     * @Rest\Get("/{channelId}")
     * @Rest\View("")
     *
     * @param string $channelId.
     *
     * @return \Bpi\RestMediaTypeBundle\Channels | XmlError
     */
    public function getChannelInfoAction($channelId) {
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');

        $channel = $channelRepository->find($channelId);

        if (null === $channel) {
            $xmlError = $this->get('bpi.presentation.xmlerror');
            $xmlError->setCode(404);
            $xmlError->setError('Channel with id ' . $channelId . ' not found.');
            return $xmlError;
        }

        $xml = $this->get('bpi.presentation.channels');
        $xml->addChannel($channel);

        return $xml;
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
        $xmlError = $this->get('bpi.presentation.xmlerror');

        if (!isset($userId) || empty($userId)) {
            $xmlError->setCode(400);
            $xmlError->setError('User external id required for listing channels.');
            return $xmlError;
        }

        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');

        $userAgency = $this->getAgencyFromHeader();
        $userAgencyId = $userAgency->getAgencyId()->id();

        if (null === $userAgency) {
            $xmlError->setCode(400);
            $xmlError->setError('Agency with external id ' . $userId . ' not found.');
            return $xmlError;
        }

        $user = $userRepository->findOneBy(
            array(
                'id' => $userId,
                'userAgency.$id' => new \MongoId($userAgency->getId())
            )
        );

        if (null === $user) {
            $xmlError->setCode(400);
            $xmlError->setError('User with given externalId: ' . $userId . ' and agency public_id: ' . $userAgencyId . ' not found.');
            return $xmlError;
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
        $xmlError = $this->get('bpi.presentation.xmlerror');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        $user = $userRepository->findOneById($params['editorId']);
        if ($user === null) {
            $xmlError->setCode(404);
            $xmlError->setError("User with id = '{$params['editorId']}' not found.");
            return $xmlError;
        }

        $channel = new Channel();
        $channel->setChannelName($params['name']);
        $channel->setChannelAdmin($user);

        if (isset($params['channelDescription']) && !empty($params['channelDescription'])) {
            $channel->setChannelDescription($params['channelDescription']);
        }

        $dm->persist($channel);
        $dm->flush();

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transform($channel);

        return $document;
    }

    /**
     * @param string $channelId.
     *
     * @Rest\Post("/edit/{channelId}")
     * @Rest\View()
     *
     * @return XmlError
     */
    public function editChannelAction($channelId) {
        $xmlError = $this->get('bpi.presentation.xmlerror');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        $channel = $channelRepository->find($channelId);
        if (null === $channel) {
            $xmlError->setCode(404);
            $xmlError->setError("Channel with id = '{$channelId}' not found.");
            return $xmlError;
        }

        $channel->setChannelName($params['channelName']);
        $channel->setChannelDescription($params['channelDescription']);

        $dm->persist($channel);
        $dm->flush();

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transform($channel);

        return $document;
    }

    /**
     * Add editors to channels
     *
     * @Rest\Post("/add/editor")
     * @Rest\View()
     */
    public function addEditorToChannelAction()
    {
        $xmlError = $this->get('bpi.presentation.xmlerror');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        // Check channel exist, load it.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            $xmlError->setCode(404);
            $xmlError->setError("Channel with id = '{$params['channelId']}' not found.");
            return $xmlError;
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        if ($admin->getId() != $params['adminId']) {
            $xmlError->setCode(404);
            $xmlError->setError("User with id  = '{$params['adminId']}' can't add users to channel.");
            return $xmlError;
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
        $xmlError = $this->get('bpi.presentation.xmlerror');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        // Check channel exist, load it.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            $xmlError->setCode(404);
            $xmlError->setError("Channel with id = '{$params['channelId']}' not found.");
            return $xmlError;
        }

        // Check if user have permission to add node to channel.
        $admin = $channel->getChannelAdmin();
        if ($admin->getId() != $params['adminId']) {
            $xmlError->setCode(404);
            $xmlError->setError("User with id  = '{$params['adminId']}' can't remove users from channel.");
            return $xmlError;
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
        $xmlError = $this->get('bpi.presentation.xmlerror');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        // Try to load channel.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            $xmlError->setCode(404);
            $xmlError->setError("Channel with id  = '{$params['channelId']}' not found.");
            return $xmlError;
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
            $xmlError->setCode(404);
            $xmlError->setError("User with id  = '{$params['editorId']}' can't push to this channel.");
            return $xmlError;
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
            } catch (Exception $e) {
                $xmlError->setCode(500);
                $xmlError->setError("Internal error on adding node.");
                return $xmlError;
            }
        }

        $dm->persist($channel);
        $dm->flush();

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');
        $facetRepository->addChannelToFacet($channel->getChannelName(), $params['nodes']);

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
        $xmlError = $this->get('bpi.presentation.xmlerror');
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
                $xmlError->setCode(400);
                $xmlError->setError("Param '{$param}' is required.");
                return $xmlError;
            }
        }

        // Try to load channel.
        $channel = $channelRepository->findOneById($params['channelId']);
        if ($channel === null) {
            $xmlError->setCode(404);
            $xmlError->setError("Channel with id  = '{$params['channelId']}' not found.");
            return $xmlError;
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
            $xmlError->setCode(404);
            $xmlError->setError("User with id  = '{$params['editorId']}' can't push to this channel.");
            return $xmlError;
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
            } catch (Exception $e) {
                return new Response('Internal error on removing node.', 500);
            }
        }

        $dm->persist($channel);
        $dm->flush();

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');
        $facetRepository->removeChannelFromFacet($channel->getChannelName(), $params['nodes']);

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
        $xmlError = $this->get('bpi.presentation.xmlerror');
        $dm = $this->getDoctrineManager();
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $channel = $channelRepository->find($channelId);

        if (null === $channel) {
            $xmlError->setCode(404);
            $xmlError->setError("Channel with id  = '{$channelId}' not found.");
            return $xmlError;
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

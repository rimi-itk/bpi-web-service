<?php
/**
 * @file
 *  Channel controller class
 */

namespace Bpi\ApiBundle\Controller;

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
     * @return \HttpResponse
     */
    public function listChannelsAction()
    {
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $serializer = $this->getSerialilzer();

        $allChannels = $channelRepository->findAll();

        $serializedData = '';

        // TODO: Output xml using RestMediaTypeBundle
        return new Response($serializedData, 200);
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
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $userRepository = $this->getRepository('BpiApiBundle:Entity\User');
        $agencyRepository = $this->getRepository('BpiApiBundle:Aggregate\Agency');
        $channelData = $this->getAllRequestParameters();
        $channelName = $channelData['name'];

        $requiredChannelData = array(
            'name',
            'agencyPublicId',
            'userExternalId'
        );

        foreach ($requiredChannelData as $dataName) {
            if (!isset($channelData[$dataName]) || empty($channelData[$dataName])) {
                $errorMessage = sprintf('%s required for channel creation.', $dataName);
                $statusCode = 400;
                throw new HttpException($statusCode, $errorMessage);
            }
        }

        $similarTitle = $channelRepository->findSimilarByName($channelName);
        if ($similarTitle) {
            $errorMessage = 'Found channel with similar name.';
            $statusCode  = 409;
            throw new HttpException($statusCode, $errorMessage);
        }

        $agency = $agencyRepository->loadUserByUsername($channelData['agencyPublicId']);
        if (null === $agency) {
            $errorMessage = 'Agency with provided public id not found.';
            $statusCode = 404;
            throw new HttpException($statusCode, $errorMessage);
        }

        $user = $userRepository->findBy(array('externalId' => $channelData['userExternalId']));
        if (null === $user) {
            $errorMessage = 'User with provided external id not found.';
            $statusCode = 404;
            throw new HttpException($statusCode, $errorMessage);
        }

        $foundUser = null;
        if (count($user) > 1) {
            foreach ($user as $key => $u) {
                if ($u->getExternalId() === $channelData['userExternalId'] && $u->getUserAgency()->getId() === $channelData['agencyPublicId']) {
                    $foundUser = $u;
                    break;
                }
            }
        } else {
            $foundUser = $user[0];
        }
        if (null === $foundUser) {
            $errorMessage = 'User with provided external id and public agency id not found.';
            $statusCode = 404;
            throw new HttpException($statusCode, $errorMessage);
        }

        $channel = new Channel();
        $channel->setChannelName($channelName);
        $channel->setChannelAdmin($foundUser);

        $dm->persist($channel);
        $dm->flush();

        // TODO: Output xml using RestMediaTypeBundle
        $responseContent = sprintf('Channel with name %s and admin user %s created', $channel->getChannelName(), $channel->getChannelAdmin()->getInternalUserName());
        return new Response($responseContent, 201);
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
        $incomingData = $this->getRequestParameter('user');
        $channelId = $this->getRequestParameter('channelId');

        $requiredData = array(
            'externalEditorId',
            'agencyPublicId'
        );

        // Check if channel exist
        if (!isset($channelId) || empty($channelId)) {
            $errorMessage = sprintf('%s required to add editor to channel.', $channelId);
            $statusCode = 400;
            throw new HttpException($statusCode, $errorMessage);
        }

        // Check required data for each user
        foreach ($incomingData as $key => $data) {
            foreach ($requiredData as $reqData) {
                if (!isset($data[$reqData]) || empty($data[$reqData])) {
                    $errorMessage = sprintf('%s required to add editor to channel.', $data[$reqData]);
                    $statusCode = 400;
                    throw new HttpException($statusCode, $errorMessage);
                }
            }
        }

        // Check channel exist, load it
        $channel = $channelRepository->find($channelId);
        if (null === $channel) {
            $errorMessage = sprintf('Channel with id %s not found.', $channelId);
            $statusCode = 404;
            throw new HttpException($statusCode, $errorMessage);
        }

        // Check if user exist and assign to channel
        foreach ($incomingData as $user) {
            $u = $userRepository->findByExternalIdAgency($user['externalEditorId'], $user['agencyPublicId']);
            if (null === $u) {
                $errorMessage = sprintf('User with external id %s and agency public id %s not found.', $user['externalEditorId'], $user['agencyPublicId']);
                $statusCode = 404;
                throw new HttpException($statusCode, $errorMessage);
            }
            $channel->addChannelEditor($u);
        }

        $dm->persist($channel);
        $dm->flush();

        // TODO: Output xml using RestMediaTypeBundle
        return new Response('Editor added to channel', 200);
    }

    /**
     * Remove user from channel
     *
     * @param string $channelId
     *
     * @Rest\Delete("/user/{channelId}")
     * @Rest\View()
     */
    public function removeEditorFromChannelAction($channelId)
    {
        $incomingData = $this->getAllQueryParameters();
        $requiredData = array(

        );

        if (!$channelId) {
            throw new HttpException(404, 'Test');
        }
    }
}

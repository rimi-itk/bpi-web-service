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
        $channelData = $this->getRequestParameter('channel');
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

        $chName = filter_var($channel->getChannelName(), FILTER_SANITIZE_STRING);
        $internalUserName = filter_var($channel->getChannelAdmin()->getInternalUserName(), FILTER_SANITIZE_STRING);

        // TODO: Output xml using RestMediaTypeBundle
        $responseContent = sprintf('Channel with name %s and admin user %s created', $chName, $internalUserName);
        return new Response($responseContent, 201);
    }
}

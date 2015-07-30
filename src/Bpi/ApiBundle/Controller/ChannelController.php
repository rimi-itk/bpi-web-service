<?php
/**
 * @file
 *  Channel controller class
 */

namespace Bpi\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

use FOS\RestBundle\Controller\FOSRestController;
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
class ChannelController extends FOSRestController
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
        foreach ($allChannels as $channel) {
            $serializedData .= $serializer->serialize($channel, 'xml');
        }

//        $serializedData = $serializer->serialize($allChannels, 'xml');

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
        $serializer = $this->getSerialilzer();
        $channelRepository = $this->getRepository('BpiApiBundle:Entity\Channel');
        $channelData = $this->getRequestParameter('channel');
        $channelName = $channelData['name'];

        $similarTitle = $channelRepository->findSimilarByName($channelName);
        if ($similarTitle) {
            // HTTP error 409 Conflict
            return new Response('Found channel with similar name.', 409);
        }

        $channel = new Channel();
        $channel->setChannelName($channelName);

        $responseContent = $serializer->serialize($channel, 'xml');

        $dm->persist($channel);
        $dm->flush();

        return new Response($responseContent, 201);
    }
}

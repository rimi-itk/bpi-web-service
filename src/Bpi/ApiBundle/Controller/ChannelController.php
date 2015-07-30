<?php
/**
 * @file
 *  Channel controller class
 */

namespace Bpi\ApiBundle\Controller;

use Bpi\ApiBundle\Domain\Entity\Channel;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

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

    /**
     * Load repository by name
     *
     * @param $repositoryName string name of repository which should be loaded
     * @return \Doctrine\ODM\MongoDB\DocumentRepository
     */
    private function getRepository($repositoryName)
    {
        return $this->get('doctrine.odm.mongodb.document_manager')->getRepository($repositoryName);
    }

    /**
     * Get doctrine mongodb manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    private function getDoctrineManager()
    {
        return $this->get('doctrine_mongodb')->getManager();
    }

    /**
     * Get all GET parameters from current request
     *
     * @return array of GET parameters
     */
    private function getAllQueryParameters()
    {
        return $this->getRequest()->query->all();
    }

    /**
     * Get parameter by name from GET request
     *
     * @param $parameterName string name of GET parameter to search
     * @return mixed
     */
    private function getQueryParameter($parameterName)
    {
        return $this->getRequest()->request->get($parameterName, false);
    }

    /**
     * Get all POST parameters of current request
     *
     * @return array of POST parameters
     */
    private function getAllRequestParameters()
    {
        return $this->getRequest()->request->all();
    }

    /**
     * Get parameter by name from POST request
     *
     * @param $parameterName
     * @return mixed
     */
    private function getRequestParameter($parameterName)
    {
        return $this->getRequest()->request->get($parameterName, false);
    }

    /**
     * Get new serializer object
     *
     * @return Serializer
     */
    private function getSerialilzer()
    {
        $encoders = array(new XmlEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer;
    }
}

<?php
/**
 * @file
 *  Controller with helper methods
 */

namespace Bpi\ApiBundle\Controller;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class BPIController
 * @package Bpi\ApiBundle\Controller
 *
 * Controller for some useful controller methods
 */
class BPIController extends FOSRestController
{
    /**
     * Load repository by name
     *
     * @param $repositoryName string name of repository which should be loaded
     * @return \Doctrine\ODM\MongoDB\DocumentRepository
     */
    protected function getRepository($repositoryName)
    {
        return $this->get('doctrine.odm.mongodb.document_manager')->getRepository($repositoryName);
    }

    /**
     * Get doctrine mongodb manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function getDoctrineManager()
    {
        return $this->get('doctrine_mongodb')->getManager();
    }

    /**
     * Get all GET parameters from current request
     *
     * @return array of GET parameters
     */
    protected function getAllQueryParameters()
    {
        return $this->getRequest()->query->all();
    }

    /**
     * Get parameter by name from GET request
     *
     * @param $parameterName string name of GET parameter to search
     * @return mixed
     */
    protected function getQueryParameter($parameterName)
    {
        return $this->getRequest()->request->get($parameterName, false);
    }

    /**
     * Get all POST parameters of current request
     *
     * @return array of POST parameters
     */
    protected function getAllRequestParameters()
    {
        return $this->getRequest()->request->all();
    }

    /**
     * Get parameter by name from POST request
     *
     * @param $parameterName
     * @return mixed
     */
    protected function getRequestParameter($parameterName)
    {
        return $this->getRequest()->request->get($parameterName, false);
    }

    /**
     * Get new serializer object
     *
     * @return Serializer
     */
    protected function getSerialilzer()
    {
        $encoders = array(new XmlEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer;
    }

    /**
     * Chekc if params is presented and how many times.
     *
     * @param $input
     * @param $required
     */
    protected function checkParams($input, &$required)
    {
        array_walk_recursive($input, function($i, $k) use (&$required)  {
            if (in_array($k, array_keys($required)) && !empty($i))
                $required[$k]++;
        });

    }
    /**
     * Strips all params.
     *
     * @param $input
     */
    protected function stripParams(&$input)
    {
        array_walk_recursive($input, function(&$i, $k) {
            $i = htmlspecialchars($i, ENT_QUOTES, 'UTF-8');
        });
    }

    /**
     * Gets agencyExternalId from headers of request.
     *
     * @return mixed agency
     */
    public function getAgencyFromHeader() {
        $request = $this->getRequest();
        $id = null;
        if ($request->headers->has('Auth')) {
            preg_match('~BPI agency="(?<agency>[^"]+)", token="(?<token>[^"]+)"~i', $request->headers->get('Auth'), $matches);
            $id = $matches['agency'];
        }

        $agency = $this->getRepository('BpiApiBundle:Aggregate\Agency')->findByPublicId($id);
        return $agency;
    }
}

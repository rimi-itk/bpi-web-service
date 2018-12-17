<?php

namespace Bpi\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class BaseBpiTest.
 */
abstract class AbstractBaseBpiTest extends WebTestCase
{
    use ContainerAwareTrait;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->registry = $this->container->get('doctrine_mongodb');
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        return \AppKernel::class;
    }
}

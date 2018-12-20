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

    /**
     * Asserts missing authentication XML response.
     *
     * @param string $rawResult Raw request result.
     */
    public function assertBpiMissingAuthentication($rawResult)
    {
        $xml = new \SimpleXMLElement($rawResult);
        $this->assertEquals('SimpleXMLElement', get_class($xml));

        $expectedXml = '<result><![CDATA[Authorization required (none)]]></result>';
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml->asXML());
    }
}

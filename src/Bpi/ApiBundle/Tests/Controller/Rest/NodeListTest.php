<?php

namespace Bpi\ApiBundle\Tests\Controller\Rest;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\RestMediaTypeBundle\Document;

class RouterStub implements \Symfony\Component\Routing\Generator\UrlGeneratorInterface
{
    public function generate($name, $parameters = array(), $absolute = false)
    {
        return 'test_generated_route';
    }

    public function setContext(\Symfony\Component\Routing\RequestContext $context) {}
    public function getContext() {}
}

class NodeListTest extends WebTestCase
{
    protected $client;
    protected $serializer;

    public function __construct()
    {
        parent::__construct();
        $this->setUp();
        $this->serializer = static::$kernel->getContainer()->get('serializer');
    }

    public function setUp()
    {
        $this->client = static::createClient(array(
              'environment' => 'test_skip_auth'
        ));
    }

    protected function doRequest(Document $doc)
    {
        $this->client->request('POST', '/node/list.bpi', array(), array(), array( 'HTTP_Content_Type' => 'application/vnd.bpi.api+xml'), $this->serializer->serialize($doc, "xml"));
    }

    protected function createDocument()
    {
        $doc = new Document;
        $doc->setRouter(new RouterStub());
        return $doc;
    }

    public function testGetDescSortedList()
    {
        $doc = $this->createDocument();
        $doc->appendEntity($query = $doc->createEntity('nodes_query'));
        $query->addProperty($doc->createProperty('sort[resource.title]', 'string', 'DESC'));

        $this->doRequest($doc);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        $titles = $xml->xpath('//entity[@name="node"]/entity[@name="resource"]/properties/property[@name="title"]');

        $this->assertEquals('charlie_title', (string)$titles[0]);
        $this->assertEquals('bravo_title', (string)$titles[1]);
        $this->assertEquals('alpha_title', (string)$titles[2]);
    }

    public function testGetAscSortedList()
    {
        $doc = $this->createDocument();
        $doc->appendEntity($query = $doc->createEntity('nodes_query'));
        $query->addProperty($doc->createProperty('sort[resource.title]', 'string', 'ASC'));

        $this->doRequest($doc);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        $titles = $xml->xpath('//entity[@name="node"]/entity[@name="resource"]/properties/property[@name="title"]');

        $this->assertEquals('charlie_title', (string)$titles[2]);
        $this->assertEquals('bravo_title', (string)$titles[1]);
        $this->assertEquals('alpha_title', (string)$titles[0]);
    }

    public function testGetLimitedList()
    {
        $doc = $this->createDocument();
        $doc->appendEntity($query = $doc->createEntity('nodes_query'));
        $query->addProperty($doc->createProperty('amount', 'number', 1));

        $this->doRequest($doc);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($this->client->getResponse()->getContent());

        $this->assertEquals(1, count($xml->children()));
    }

    public function testGetListWithOffset()
    {
        $doc = $this->createDocument();
        $doc->appendEntity($query = $doc->createEntity('nodes_query'));
        $query->addProperty($doc->createProperty('amount', 'number', 1));
        $query->addProperty($doc->createProperty('offset', 'number', 1));

        $this->doRequest($doc);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($this->client->getResponse()->getContent());

        $titles = $xml->xpath('//entity[@name="node"]/entity[@name="resource"]/properties/property[@name="title"]');
        $this->assertEquals(1, count($titles));
        $this->assertEquals('bravo_title', (string)$titles[0]);
    }

    public function testFilterList()
    {
        $doc = $this->createDocument();
        $doc->appendEntity($query = $doc->createEntity('nodes_query'));
        $query->addProperty($doc->createProperty('filter[profile.category]', 'string', 'category_A'));

        $this->doRequest($doc);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        $titles = $xml->xpath('//entity[@name="node"]/entity[@name="resource"]/properties/property[@name="title"]');

        $this->assertEquals('alpha_title', (string)$titles[0]);
        $this->assertEquals('charlie_title', (string)$titles[1]);
    }

    public function testSelfLink()
    {
        $doc = $this->createDocument();
        $doc->appendEntity($query = $doc->createEntity('nodes_query'));

        $this->doRequest($doc);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        $linlk = $xml->xpath('//entity[@name="node"]/links/link[@rel="self"]');

        $this->client->request('GET', $linlk[0]['href'].'.bpi');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        $this->assertEquals(1, $xml->entity->count());
        $this->assertTrue(isset($xml->entity->properties));
    }
}

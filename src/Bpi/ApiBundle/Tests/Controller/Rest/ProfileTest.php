<?php

namespace Bpi\ApiBundle\Tests\Controller\Rest;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\RestMediaTypeBundle\Document;

class ProfileTest extends WebTestCase
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
        $doc->setRouter($this->getMock('\Symfony\Component\Routing\Generator\UrlGeneratorInterface'));
        return $doc;
    }

    protected function fetchAlphaNode()
    {
        $doc = $this->createDocument();
        $doc->appendEntity($query = $doc->createEntity('nodes_query'));
        $this->doRequest($doc);

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        $linlk = $xml->xpath('//entity[@name="node"]/links/link[@rel="self"]');

        $this->client->request('GET', $linlk[0]['href'].'.bpi');
        return simplexml_load_string($this->client->getResponse()->getContent());
    }

    public function testFields()
    {
        $xml = $this->fetchAlphaNode();

        // assert param
        $this->assertNotEmpty(current($xml->xpath('//entity[@name="node"]/properties/property[@name="editable"]')));

        // assert profile
        $yearwheel = current($xml->xpath('//entity/entity[@name="profile"]/properties/property[@name="yearwheel"]'));
        $this->assertEquals('Winter', (string)$yearwheel);
        $audience = current($xml->xpath('//entity/entity[@name="profile"]/properties/property[@name="audience"]'));
        $this->assertEquals('audience_A', (string)$audience);
        $category = current($xml->xpath('//entity/entity[@name="profile"]/properties/property[@name="category"]'));
        $this->assertEquals('category_A', (string)$category);
        $tags = current($xml->xpath('//entity/entity[@name="profile"]/properties/property[@name="tags"]'));
        $this->assertEquals('foo, bar, zoo', (string)$tags);

        // assert body
        $type = current($xml->xpath('//entity/entity[@name="resource"]/properties/property[@name="type"]'));
        $this->assertEquals('article', (string)$type);
        $body = current($xml->xpath('//entity/entity[@name="resource"]/properties/property[@name="body"]'));
        $this->assertEquals(1, preg_match('~^<p>alpha_body</p>~', (string)$body), 'At least first line of body must much');
        $this->assertEquals(1, preg_match('~<p>Originally published by George Bush, Aarhus Kommunes Biblioteker.</p>$~', (string)$body), 'Copyleft doesn\'t exists');
    }
}

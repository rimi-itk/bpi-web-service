<?php

namespace Bpi\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestControllerTest extends WebTestCase
{
    protected $console;
    protected $load_fixtures;

    protected function loadFixture($name)
    {
        return file_get_contents(__DIR__.'/../Fixtures/'.$name.'.bpi');
    }

    public function __construct()
    {
        $this->console = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->createKernel());
        $this->console->setAutoExit(false);
        $this->load_fixtures = new \Symfony\Component\Console\Input\ArrayInput(array(
            "--env" => "test",
            "--quiet" => true,
            "--fixtures" => 'src/Bpi/ApiBundle/Tests/DoctrineFixtures',
            'command' => 'doctrine:mongodb:fixtures:load'
        ));
        $this->console->run($this->load_fixtures);
    }

    public function doRequest($uri, $body, $method = 'POST')
    {
        $client = static::createClient(array(
              'environment' => 'test_skip_auth'
        ));
        $client->request($method, $uri, array(), array(), array( 'HTTP_Content_Type' => 'application/vnd.bpi.api+xml'), $body);
        return $client;
    }

    public function testAnswerBadRequestOnPostMalformedRequest()
    {
        // empty request
        try {
            $client = $this->doRequest('/node.bpi', '');
            $this->fail('HTTP Exception expected');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode(), 'Response must be 400 Bad Request');
        }

        // bad xml request
        try {
            $client = $this->doRequest('/node.bpi', '<foo>');
            $this->fail('HTTP Exception expected');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode(), 'Response must be 400 Bad Request');
        }

        // non xml request
        try {
            $client = $this->doRequest('/node.bpi', 'foo');
            $this->fail('HTTP Exception expected');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode(), 'Response must be 400 Bad Request');
        }
    }

    public function testPublish()
    {
        $client = $this->doRequest('/node.bpi', $this->loadFixture('Push'));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($client->getResponse()->getContent());
        $this->assertEquals('node', $xml->entity['name']);

        // assert body
        $body = current($xml->xpath('//entity/entity[@name="resource"]/properties/property[@name="body"]'));
        $this->assertEquals(1, preg_match('~^<p>foo<span>bar</span></p>~', (string)$body), 'At least first line of body must much');
        $this->assertEquals(1, preg_match('~<img id="embedded_img" src="(.+)"~', (string)$body, $matches), 'embedded_img not found');
        $this->assertEquals(1, preg_match("~.+~", $matches[1]), 'src is empty');

        $this->console->run($this->load_fixtures);
    }

    public function testPublishRevision()
    {


        // find first node
        $client = $this->doRequest('/node/list.bpi', $this->loadFixture('NodesQuery/FindOne'));
        $xml = simplexml_load_string($client->getResponse()->getContent());
        $links = $xml->xpath('//entity[@name="node"]/links/link[@rel="self"]');

        $this->assertNotEmpty($links[0]['href']);

        // push revision
        /** @todo modify resource from response */
        $client = $this->doRequest($links[0]['href'].'.bpi', $this->loadFixture('PushRevision'));
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $xml = simplexml_load_string($client->getResponse()->getContent());
        $this->assertEquals('node', $xml->entity['name']);

        $this->console->run($this->load_fixtures);
    }
}

<?php

namespace Bpi\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestControllerTest extends WebTestCase
{
    protected $console;
    protected $load_fixtures;

    protected function loadFixture($name)
    {
        $fixture = file_get_contents(__DIR__.'/../Fixtures/'.$name.'.bpi');

        if ($name == 'Push')
        {
            return $this->replaceAgencyIdWithActualValue($fixture);
        }

        return $fixture;
    }

    protected function replaceAgencyIdWithActualValue($fixture)
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $dm = static::$kernel->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $query = $dm->getRepository('BpiApiBundle:Aggregate\Agency')->createQueryBuilder();
        $real_agency_id = $query->limit(1)->getQuery()->execute()->getSingleResult()->getAgencyId()->id();
        return str_replace('stub_agency_id', $real_agency_id, $fixture);
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

    public function testSkipAuthorizationForOptionsRequest()
    {
        $client = static::createClient();

        $client->request('GET', '/node/list.xml');
        $this->assertEquals(401, $client->getResponse()->getStatusCode(), 'Response must be 401 Not authorized');

        $client->request('OPTIONS', '/node/list.xml');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Response must be 200 Ok');
    }

    public function testProfileDictionary()
    {
        $client = $this->doRequest('/profile_dictionary.xml', '', 'GET');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $xml = simplexml_load_string($client->getResponse()->getContent());
        $entity = $xml->xpath('//entity[@name="profile_dictionary"]');
        $this->assertEquals(1, count($entity));
    }

    public function testPublish()
    {
        $client = $this->doRequest('/node.bpi', $this->loadFixture('Push'));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        // just ensure that service echoes back same structure
        $xml = simplexml_load_string($client->getResponse()->getContent());
        $this->assertEquals('node', $xml->entity['name']);
        $this->assertNotEmpty(current($xml->xpath('//entity/entity[@name="profile"]')));
        $this->assertNotEmpty(current($xml->xpath('//entity/entity[@name="resource"]')));

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

        // assert body
        $body = current($xml->xpath('//entity/entity[@name="resource"]/properties/property[@name="body"]'));
        $this->assertEquals(0, preg_match('~<p>Originally published by Agency Alpha.</p>$~', (string)$body), 'Copyleft doesn\'t exists');

        $this->console->run($this->load_fixtures);
    }
}

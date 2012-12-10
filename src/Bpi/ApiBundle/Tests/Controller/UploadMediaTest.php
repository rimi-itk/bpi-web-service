<?php

namespace Bpi\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UploadMediaTest extends WebTestCase
{
    protected $console;
    protected $load_fixtures;

    protected function loadFixture($name, $extension = 'bpi')
    {
        return file_get_contents(__DIR__.'/../Fixtures/'.$name.'.'.$extension);
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

    public function testSendLargeRequest()
    {
        try{
            $client = $this->doRequest('/node.bpi', $this->loadFixture('Assets/EmbededOverflow'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(413, $e->getStatusCode());
        }
    }

    public function testPostAssetRequest()
    {
        // find first node
        $client = $this->doRequest('/node/list.bpi', $this->loadFixture('NodesQuery/FindOne'));
        $xml = simplexml_load_string($client->getResponse()->getContent());

        $links = $xml->xpath('//entity[@name="node"]/links/link[@rel="assets"]'); // assets relations
        $this->assertNotEmpty($links[0]['href']);

        // push revision
        $image = $this->loadFixture('Assets/img', 'gif');
        $image_name = mt_rand().'.gif';
        $client = $this->doRequest($links[0]['href'].'/'.$image_name, $image, 'PUT');
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResponse()->getContent());

        /** @todo try to get resource back from server */
    }

    /**
     * @todo test replacement of file / uniqueness
     */
}

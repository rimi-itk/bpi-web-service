<?php

namespace Bpi\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestControllerTest extends WebTestCase
{
	public function testPublish()
	{
		$article = file_get_contents(__DIR__.'/../Fixtures/Article.bpi');
		$client = static::createClient();
		
		$client->request(	'POST', '/node.bpi', array(), array(), array( 'HTTP_Content_Type' => 'application/vnd.bpi.api+xml'), $article);
		$this->assertEquals(201, $client->getResponse()->getStatusCode());

		$xml = simplexml_load_string($client->getResponse()->getContent());
		$this->assertEquals('node', $xml->entity['name']);
		$this->assertNotEmpty('id', $xml->entity->properties->property['title']);
	}
}
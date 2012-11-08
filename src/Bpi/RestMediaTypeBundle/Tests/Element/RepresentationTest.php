<?php

namespace Bpi\RestMediaTypeBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\RestMediaTypeBundle\Element\Link;
use Bpi\RestMediaTypeBundle\Element\Entity;
use Bpi\RestMediaTypeBundle\Element\Collection;

class RepresentationTest extends WebTestCase
{
	/**
	 * @var \JMS\SerializerBundle\Serializer\Serializer
	 */
	protected $serializer;
	
	public function __construct()
	{
		static::$kernel = static::createKernel(array());
		static::$kernel->boot();
		$this->serializer = static::$kernel->getContainer()->get('serializer');
	}
	
	public function __destruct()
	{
		static::$kernel->shutdown();
	}

	public function testProperty()
	{
		$xml = simplexml_load_string($this->serializer->serialize(new Property('foo', 'bar', 'zoo'), "xml"));
		
		$this->assertEquals($xml->getName(), 'property');
		$this->assertEquals($xml['type'], 'foo');
		$this->assertEquals($xml['name'], 'bar');
		$this->assertEquals((string)$xml, 'zoo');
	}
	
	public function testLink()
	{
		$xml = simplexml_load_string($this->serializer->serialize(new Link('foo', 'bar', 'zoo'), "xml"));
		
		$this->assertEquals($xml->getName(), 'link');
		$this->assertEquals($xml['rel'], 'foo');
		$this->assertEquals($xml['href'], 'bar');
		$this->assertEquals($xml['title'], 'zoo');
		$this->assertEmpty((string)$xml);
	}
	
	public function testEntity()
	{
		$entity = new Entity('foo');
		$entity->addProperty(new Property('type', 'name', 'value'));
		$xml = simplexml_load_string($this->serializer->serialize($entity, "xml"));
		
		$this->assertEquals($xml->getName(), 'entity');
		$this->assertEquals($xml['name'], 'foo');
		$this->assertEquals(1, count($xml->properties));
	}
	
	public function testCollection()
	{
		$collection = new Collection('bar');
		$collection->add(new Entity('foo'));
		$xml = simplexml_load_string($this->serializer->serialize($collection, "xml"));
		
		$this->assertEquals($xml->getName(), 'collection');
		$this->assertEquals($xml['name'], 'bar');
		$this->assertEquals(1, count($xml->items));
	}
	
	public function testHasLink()
	{
		$collection = new Collection('bar');
		$collection->addLink(new Link('foo', 'bar', 'zoo'));
		$xml = simplexml_load_string($this->serializer->serialize($collection, "xml"));
		
		$this->assertEquals(1, count($xml->links));
		foreach($xml->links->children() as $link)
			$this->assertEquals('link', $link->getName());
		
		$entity = new Entity('bar');
		$entity->addLink(new Link('foo', 'bar', 'zoo'));
		$xml = simplexml_load_string($this->serializer->serialize($entity, "xml"));
		
		$this->assertEquals(1, count($xml->links));
		foreach($xml->links->children() as $link)
			$this->assertEquals('link', $link->getName());
	}
}
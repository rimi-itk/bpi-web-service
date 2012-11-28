<?php

namespace Bpi\RestMediaTypeBundle\Tests\Property;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\RestMediaTypeBundle\Property\Entity as EntityProperty;
use Bpi\RestMediaTypeBundle\Element\Entity as EntityElement;
use Bpi\RestMediaTypeBundle\DataType\Entity as EntityType;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\RestMediaTypeBundle\DataType\String;

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

    public function testEntity()
    {
        $entity = new EntityElement('bar');
        $entity->addProperty(new Property('a', 'b', 'c'));
        $property = new EntityProperty(new String('foo'), new EntityType($entity), new String('zoo'));

        $xml = simplexml_load_string($this->serializer->serialize($property, "xml"));

        $this->assertEquals('property', $xml->getName());
        $this->assertEquals('entity', $xml['type']);
        $this->assertEquals('foo', $xml['name']);
        $this->assertEquals('zoo', $xml['title']);
        $this->assertEquals('bar', $xml['entityname']);
        $this->assertEquals(1, $xml->children()->count());

        foreach ($xml->children() as $child) {
            $this->assertEquals('entity', $child->getName());
            $this->assertEquals('bar', $child['name']);
            $this->assertEquals(1, $child->properties->count());
        }
    }

    public function testMultipleEntities()
    {
        $entity = new EntityElement('bar');
        $entity2 = new EntityElement('bar');
        $property = new EntityProperty(
            new String('foo'),
            new EntityType(array($entity, $entity2)),
            new String('zoo')
        );

        $unserialized = simplexml_load_string($this->serializer->serialize($property, "xml"));

        $this->assertEquals('property', $unserialized->getName());
        $this->assertEquals('entity', $unserialized['type']);
        $this->assertEquals('foo', $unserialized['name']);
        $this->assertEquals('zoo', $unserialized['title']);
        $this->assertEquals('bar', $unserialized['entityname']);
        $this->assertEquals(2, $unserialized->children()->count());

        foreach ($unserialized->children() as $child) {
            $this->assertEquals('entity', $child->getName());
            $this->assertEquals('bar', $child['name']);
        }
    }
}

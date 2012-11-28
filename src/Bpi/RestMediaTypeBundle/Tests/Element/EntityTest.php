<?php
namespace Bpi\RestMediaTypeBundle\Tests;

use Bpi\RestMediaTypeBundle\Element\Entity;
use Bpi\RestMediaTypeBundle\Element\Property;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    public function testHasProperty()
    {
        $entity = new Entity('foo');
        $entity->addProperty(new Property('a', 'b', 'c'));

        $this->assertTrue($entity->hasProperty('b'));
    }
}

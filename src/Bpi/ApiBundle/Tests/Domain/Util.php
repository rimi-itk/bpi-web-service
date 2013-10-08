<?php

namespace Bpi\ApiBundle\Tests\Domain;

use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

/**
 * Helper class
 */
class Util extends \PHPUnit_Framework_TestCase
{
	/**
	 * Helper method to group and incapsulate creation of resource builder
	 *
	 * @return Bpi\ApiBundle\Domain\Factory\ResourceBuilder
	 */
    public function createResourceBuilder()
    {
        $fs = $this->getMockBuilder('\Gaufrette\Filesystem')
            ->setConstructorArgs(array(new \Gaufrette\Adapter\InMemory))
            ->getMock()
        ;

        $router = $this->getMockBuilder('\Symfony\Component\Routing\RouterInterface')
            ->getMock()
        ;

        return new ResourceBuilder($fs, $router);
    }
}
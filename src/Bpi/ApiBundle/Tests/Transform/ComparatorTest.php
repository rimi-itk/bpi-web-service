<?php
namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\Transform\Comparator;

class ComparatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareStrings()
    {
        $cmp = new Comparator('a', 'b', 1);
        $this->assertEquals(-1, $cmp->getResult());

        $cmp = new Comparator('a', 'b', -1);
        $this->assertEquals(1, $cmp->getResult());
    }

    public function testCompareDates()
    {
        $cmp = new Comparator(new \DateTime("yesterday"), new \DateTime("tomorrow"), 1);
        $this->assertEquals(-1, $cmp->getResult());

        $cmp = new Comparator(new \DateTime("yesterday"), new \DateTime("tomorrow"), -1);
        $this->assertEquals(1, $cmp->getResult());

        $cmp = new Comparator(new \DateTime("now"), new \DateTime("now"), 1);
        $this->assertEquals(0, $cmp->getResult());

        $cmp = new Comparator(new \DateTime("now"), new \DateTime("now"), -1);
        $this->assertEquals(0, $cmp->getResult());
    }
}

<?php
namespace Bpi\ApiBundle\Tests\SDK;

class EndUserTest extends SDKTestCase
{
    public function testBase()
    {
        $bpi = new \Bpi\Sdk\Bpi('http://bpi1.inlead.dk', mt_rand(), mt_rand(), mt_rand());
        $list = $bpi->searchNodes();

        $this->assertTrue((bool)$list->count());

        foreach($list as $item)
        {
            $this->assertTrue((bool) count($item->getProperties()));
        }
    }
}

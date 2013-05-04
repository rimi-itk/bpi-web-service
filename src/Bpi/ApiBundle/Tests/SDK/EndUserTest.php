<?php
namespace Bpi\ApiBundle\Tests\SDK;

class EndUserTest extends SDKTestCase
{
    public function testNodeList()
    {
        $bpi = new \Bpi('http://bpi1.inlead.dk', mt_rand(), mt_rand(), mt_rand());
        $list = $bpi->searchNodes();

        $this->assertTrue((bool)$list->count());

        foreach($list as $item)
        {
            $this->assertTrue((bool) count($item->getProperties()));
        }
    }

    public function testPush()
    {
        $bpi = new \Bpi('http://bpi1.inlead.dk', mt_rand(), mt_rand(), mt_rand());
        $dt = new \DateTime();

        $node = $bpi->push($data = array(
            'title' => 'title_' . mt_rand(),
            'body' => 'body_' . mt_rand(),
            'teaser' => 'teaser_' . mt_rand(),
            'type' => 'type' . mt_rand(),
            'creation' => $dt->format(\DateTime::W3C),
            'category' => 'category',
            'audience' => 'all',
            'editable' => 1,
            'authorship' => 1,
            'agency_id' => '200100', // this value must exists, otherwise it will fail
            'local_id' =>  mt_rand(),
            'firstname' => 'firstname' . mt_rand(),
            'lastname' => 'lastname' . mt_rand(),
        ));

        $peoperties = $node->getProperties();
        foreach($data as $key => $val)
        {
            $this->assertEquals($val, $peoperties[$key]);
        }
    }
}

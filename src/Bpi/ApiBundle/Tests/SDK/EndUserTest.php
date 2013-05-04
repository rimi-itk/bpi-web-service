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
            'type' => 'article',
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
            if ($key == 'body')
                continue;
            $this->assertEquals($val, $peoperties[$key]);
        }
    }

    public function testGetNode()
    {
        $bpi = new \Bpi(self::TEST_ENDPOINT_URI, mt_rand(), mt_rand(), mt_rand());
        try
        {
            $bpi->getNode(mt_rand());
            $this->fail('ClientError exception expected');
        }
        catch(\Bpi\Sdk\Exception\HTTP\ClientError $e)
        {
            $this->assertTrue(true);
        }

        $list = $bpi->searchNodes(array('amount' => 1));
        $properties = $list->current()->getProperties();

        $node = $bpi->getNode($properties['id']);
        $this->assertEquals($properties, $node->getProperties());
    }

    public function testStatistics()
    {
        $bpi = new \Bpi('http://bpi1.inlead.dk', mt_rand(), mt_rand(), mt_rand());
        $stats = $bpi->getStatistics('2013-05-01', '2013-05-05');

        $results = $stats->getProperties();
        $this->assertTrue(isset($results['push']));
        $this->assertTrue(isset($results['syndicate']));
    }
}

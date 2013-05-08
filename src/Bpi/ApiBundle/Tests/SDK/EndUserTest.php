<?php
namespace Bpi\ApiBundle\Tests\SDK;

class EndUserTest extends SDKTestCase
{
    protected $bpi;

    public function setUp()
    {
        parent::setUp();
        $this->bpi = $this->createBpi();
        $this->bpi_bravo = $this->createBpiBravo();
    }

    public function testNodeList()
    {
        $list = $this->bpi->searchNodes();

        $this->assertTrue((bool)$list->count());

        foreach($list as $item)
        {
            $this->assertTrue((bool) count($item->getProperties()));
        }
    }

    public function testPush()
    {
        $node = $this->bpi->push($data = $this->createRandomDataForPush());

        $properties = $node->getProperties();
        foreach($data as $key => $val)
        {
            // Ignore some fields
            if (in_array($key, array('body', 'authorship', 'local_id', 'firstname', 'lastname')))
                continue;

            $this->assertEquals($val, $properties[$key]);
        }

        // These fields are generated after the push
        $this->assertTrue(!empty($properties['id']));
        $this->assertTrue(!empty($properties['pushed']));
        $this->assertTrue(!empty($properties['author']));
    }

    public function testGetNode()
    {
        try
        {
            $this->bpi->getNode(mt_rand());
            $this->fail('ClientError exception expected');
        }
        catch(\Bpi\Sdk\Exception\HTTP\ClientError $e)
        {
            $this->assertTrue(true);
        }

        $list = $this->bpi->searchNodes(array('amount' => 1));
        $properties = $list->current()->getProperties();

        $node = $this->bpi->getNode($properties['id']);
        $this->assertEquals($properties, $node->getProperties());
    }

    public function testStatistics()
    {
        $stats = $this->bpi->getStatistics('2013-05-01', '2013-05-05');

        $results = $stats->getProperties();
        $this->assertTrue(isset($results['push']));
        $this->assertTrue(isset($results['syndicate']));
    }

    public function testDictionaries()
    {
        $dict = $this->bpi->getDictionaries();

        $this->assertTrue((bool) count($dict['audience']));
        $this->assertTrue((bool) count($dict['category']));
    }

    /** @todo move to user story spec */
    public function testDeleteByNotOwner()
    {
        $node = $this->bpi->push($data = $this->createRandomDataForPush());
        $data = $node->getProperties();

        try {
            $this->bpi_bravo->deleteNode($data['id']);
            $this->fail('Exception expected');
        }
        catch(\Bpi\Sdk\Exception\HTTP\ClientError $e)
        {
            $this->assertTrue(true);
        }
    }
}

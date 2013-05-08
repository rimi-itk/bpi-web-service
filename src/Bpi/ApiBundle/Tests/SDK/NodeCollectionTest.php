<?php
namespace Bpi\ApiBundle\Tests\SDK;

class NodeCollectionTest extends SDKTestCase
{
    protected function getRefinementQuery()
    {
        $doc = $this->createDocument($client = new \Goutte\Client());
        $doc->loadEndpoint(self::TEST_ENDPOINT_URI);
        $doc->firstItem('name', 'node')->link('collection')->follow($doc);
        return array($doc, $doc->firstItem('type', 'collection')->query('refinement'));
    }

    public function testPaginationQuery()
    {
        list($doc, $query) = $this->getRefinementQuery();

        $query->send($doc, array('amount' => 1));
        $properties2 = array();
        $doc2 = clone $doc;
        $this->assertEquals(1, $doc2->reduceItemsByAttr('type', 'entity')->count());
        $doc2->walkProperties(function($e) use(&$properties2) { $properties2[] = $e; });

        $query->send($doc, array('amount' => 1, 'offset' => 1));
        $properties3 = array();
        $doc3 = clone $doc;
        $this->assertEquals(1, $doc3->reduceItemsByAttr('type', 'entity')->count());
        $doc3->walkProperties(function($e) use(&$properties3) { $properties3[] = $e; });

        $this->assertNotEquals($properties2, $properties3);
    }

    public function testFilterQuery()
    {
        list($doc, $query) = $this->getRefinementQuery();

        $query->send($doc, array('filter' => array('title' => 'bravo_title')));
        $this->assertEquals(1, $doc->reduceItemsByAttr('type', 'entity')->count());

        $query->send($doc, array('filter' => array('agency_id' => $this->auth_agency)));
        $this->assertEquals(3, $doc->reduceItemsByAttr('type', 'entity')->count());

        $query->send($doc, array('filter' => array('author' => 'Potter')));
        $this->assertEquals(2, $doc->reduceItemsByAttr('type', 'entity')->count());

        $query->send($doc, array('filter' => array('author' => 'Potter Harry')));
        $this->assertEquals(2, $doc->reduceItemsByAttr('type', 'entity')->count());

        $query->send($doc, array('filter' => array('author' => 'Harry Potter')));
        $this->assertEquals(2, $doc->reduceItemsByAttr('type', 'entity')->count());

        $query->send($doc, array('filter' => array('author' => 'Harry')));
        $this->assertEquals(1, $doc->reduceItemsByAttr('type', 'entity')->count());
    }

    public function testSearchQuery()
    {
        list($doc, $query) = $this->getRefinementQuery();

        $query->send($doc, array('search' => 't'));
        $this->assertTrue((bool) $doc->reduceItemsByAttr('type', 'entity')->count());
    }

    public function testCollectionMetadata()
    {
        $doc = $this->createDocument($client = new \Goutte\Client());
        $doc->loadEndpoint(self::TEST_ENDPOINT_URI);
        $doc->firstItem('name', 'node')->link('collection')->follow($doc);
        $coll = $doc->firstItem('type', 'collection');

        $self = $this;
        $coll->walkProperties(function($property) use($self) {
            $self->assertEquals('total', $property['name']);
            $self->assertEquals(3, $property['@value']);
        });
    }

    public function testGetDescSortedList()
    {
        list($doc, $query) = $this->getRefinementQuery();

        $query->send($doc, array('sort' => array('title' => 'DESC')));
        $this->assertEquals(3, $doc->reduceItemsByAttr('type', 'entity')->count());

        $result = array();
        foreach($doc as $item)
            $result[] = $item->propertiesToArray();

        $this->assertEquals('charlie_title', $result[0]['title']);
        $this->assertEquals('bravo_title', $result[1]['title']);
        $this->assertEquals('alpha_title unicode(❶)', $result[2]['title']);
    }

    public function testGetAscSortedList()
    {
        list($doc, $query) = $this->getRefinementQuery();

        $query->send($doc, array('sort' => array('title' => 'ASC')));
        $this->assertEquals(3, $doc->reduceItemsByAttr('type', 'entity')->count());

        $result = array();
        foreach($doc as $item)
            $result[] = $item->propertiesToArray();

        $this->assertEquals('alpha_title unicode(❶)', $result[0]['title']);
        $this->assertEquals('bravo_title', $result[1]['title']);
        $this->assertEquals('charlie_title', $result[2]['title']);
    }
}

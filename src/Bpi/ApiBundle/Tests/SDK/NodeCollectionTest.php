<?php
namespace Bpi\ApiBundle\Tests\SDK;

class NodeCollectionTest extends SDKTestCase
{
    public $valid_fields = array(
        'id',
        'creation',
        'syndication',
        'editable',
        'category',
        'audience',
        'tags',
        'title',
        'body',
        'teaser',
        'type',
        'yearwheel'
    );

    public function testNodeCollection()
    {
        $doc = $this->createDocument($client = new \Goutte\Client());
        $doc->loadEndpoint(self::TEST_ENDPOINT_URI);
        $doc->firstItem('name', 'node')->link('collection')->follow($doc);

        $self = $this;
        $doc->walkProperties(function($e) use($self) {
            $self->assertTrue(in_array($e['name'], $self->valid_fields), $e['name'] . 'is not valid property name');
            $self->assertNotEmpty($e['@value']);
        });
    }

    public function testPaginationQuery()
    {
        $doc = $this->createDocument($client = new \Goutte\Client());
        $doc->loadEndpoint(self::TEST_ENDPOINT_URI);
        $doc->firstItem('name', 'node')->link('collection')->follow($doc);
        $query = $doc->firstItem('type', 'collection')->query('pagination');

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
}

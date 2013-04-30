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
}
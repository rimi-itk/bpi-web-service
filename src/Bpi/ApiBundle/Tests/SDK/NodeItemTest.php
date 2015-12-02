<?php
namespace Bpi\ApiBundle\Tests\SDK;

class NodeItemTest extends SDKTestCase
{
    public $valid_fields = array(
        'id',
        'creation',
        'pushed',
        'editable',
        'category',
        'audience',
        'title',
        'body',
        'teaser',
        'type',
        'yearwheel',
        'author',
        'agency_id',
        'agency_name',
        'material'
    );

    /**
     *
     * @var \Bpi\Sdk\Document
     */
    protected $item;

    public function setUp()
    {
        $doc = $this->createDocument($client = new \Goutte\Client());
        $doc->loadEndpoint(self::getEndpointUri());
        $doc->firstItem('name', 'node')->link('collection')->get($doc);
        $query = $doc->firstItem('type', 'collection')->query('refinement');
        $query->send($doc, array('filter' => array('title' => 'alpha_title')));
        $this->item = $doc->firstItem('type', 'entity');
    }

    public function testPropertiesOfFirstItem()
    {
        $self = $this;
        $this->item->walkProperties(function($e) use($self) {
            $self->assertTrue(in_array($e['name'], $self->valid_fields), $e['name'] . ' is not valid property name');
            $self->assertTrue(isset($e['@value']));
        });

        $properties = $this->item->propertiesToArray();
        $this->assertEquals('Winter', $properties['yearwheel']);
        $this->assertEquals('All', $properties['audience']);
        $this->assertEquals('Event', $properties['category']);
        $this->assertEquals('article', $properties['type']);
        $this->assertEquals(
            '<p>alpha_body unicode(❶)</p><p>Originally published by George Bush, Aarhus Kommunes Biblioteker.</p>',
            $properties['body'],
            'Body or copyleft doesn\'t match'
        );
    }
}

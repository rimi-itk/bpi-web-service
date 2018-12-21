<?php

namespace Bpi\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class BaseBpiTest.
 */
abstract class AbstractBaseBpiTest extends WebTestCase
{
    use ContainerAwareTrait;

    const VALID_NODE_PROPERTIES = [
        'id' => 'string',
        'pushed' => 'dateTime',
        'editable' => 'boolean',
        'authorship' => 'boolean',
        'author' => 'string',
        'agency_id' => 'string',
        'agency_name' => 'string',
        'agency_internal' => 'boolean',
        'category' => 'string',
        'audience' => 'string',
        'syndications' => 'string',
        'title' => 'string',
        'body' => 'string',
        'teaser' => 'string',
        'creation' => 'dateTime',
        'type' => 'string',
        'url' => 'string',
        'data' => 'string',
        'material' => 'string',
    ];

    const VALID_NODE_FACETS = [
        'type',
        'tags',
        'category',
        'author',
        'audience',
        'agency_internal',
        'agency_id',
    ];

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->registry = $this->container->get('doctrine_mongodb');
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        return \AppKernel::class;
    }

    /**
     * Asserts missing authentication XML response.
     *
     * @param string $rawResult Raw request result.
     */
    public function assertBpiMissingAuthentication($rawResult)
    {
        $xml = new \SimpleXMLElement($rawResult);
        $this->assertEquals('SimpleXMLElement', get_class($xml));

        $expectedXml = '<result><![CDATA[Authorization required (none)]]></result>';
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml->asXML());
    }

    /**
     * Asserts single bpi entity structure.
     *
     * @param \SimpleXMLElement $xml BPI entity.
     */
    public function assertBpiEntity(\SimpleXMLElement $item)
    {
        $this->assertNotNull($item->attributes()['type']);
        $this->assertEquals('entity', $item->attributes()['type']);

        // Assert 'properties' tag.
        /** @var \SimpleXMLElement[] $properties */
        $properties = $item->xpath('properties');
        $this->assertCount(1, $properties);

        // Assert 'property' tags.
        /** @var \SimpleXMLElement $property */
        $property = $properties[0]->xpath('property');
        $this->assertNotEmpty($property);

        foreach (self::VALID_NODE_PROPERTIES as $validPropertyName => $validPropertyType) {
            // Assert property with required name exists.
            $propertyTag = $properties[0]->xpath('property[@name="'.$validPropertyName.'"]');
            $this->assertNotEmpty($propertyTag);

            // Assert certain property is of valid type.
            $this->assertNotNull($propertyTag[0]->attributes()['type']);
            $propertyTagTypeProperty = (string)$propertyTag[0]->attributes()['type'];

            $this->assertEquals(
                $validPropertyType,
                $propertyTagTypeProperty
            );
        }

        // Assert 'assets' tag.
        /** @var \SimpleXMLElement[] $assets */
        $assets = $item[0]->xpath('assets');
        $this->assertCount(1, $assets);

        // Assert 'tags' tag.
        /** @var \SimpleXMLElement[] $tags */
        $tags = $item[0]->xpath('tags');
        $this->assertCount(1, $tags);

        // Assert 'tag' tags.
        /** @var \SimpleXMLElement $tag */
        foreach ($tags[0]->xpath('tag') as $tag) {
            $this->assertNotNull($tag->attributes()['tag_name']);
            $this->assertNotEmpty((string)$tag->attributes()['tag_name']);
        }
    }
}

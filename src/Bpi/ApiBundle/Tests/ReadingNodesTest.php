<?php

namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\Controller\RestController;
use Bpi\ApiBundle\DataFixtures\MongoDB\AgencyFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\AudienceFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\CategoryFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\NodeFixtures;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;

/**
 * Class ReadingTest.
 */
class ReadingNodesTest extends AbstractFixtureAwareBpiTest
{
    /**
     * Authentication token.
     *
     * @var string
     */
    protected $authenticationToken;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var \Bpi\ApiBundle\Domain\Aggregate\Agency $agency */
        $agency = $this->registry->getRepository(Agency::class)->findOneBy([
            'public_id' => '999999',
        ]);
        // Store an authentication token for further requests.
        $this->authenticationToken = $this->generateAuthenticationHeader($agency);
    }

    /**
     *
     */
    public function testMain()
    {
        $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testAnonymousNodeItem()
    {
        $this->client->request('GET', '/node/item/123456');

        $rawResult = $this->client->getResponse()->getContent();

        $this->assertBpiMissingAuthentication($rawResult);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testMissingNodeItem()
    {
        $this->client->request(
            'GET',
            '/node/item/123456',
            [],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testNodeItem()
    {
        $nodeRepository = $this->registry->getRepository(Node::class);

        /** @var Node[] $nodes */
        $nodes = $nodeRepository->findAll();
        $this->assertGreaterThan(0, count($nodes));
        $nodeId = $nodes[0]->getId();

        $this->client->request(
            'GET',
            '/node/item/'.$nodeId,
            [],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);
        // Assert root tag.
        $this->assertEquals('bpi', $xml->getName());
        $rootNode = $xml;
        $this->assertNotNull($rootNode->attributes()['version']);

        /** @var \SimpleXMLElement[] $item */
        $item = $xml->xpath('item');
        $this->assertCount(1, $item);
        $this->assertBpiEntity($item[0]);
    }

    /**
     *
     */
    public function testAnonymousNodeCollection() {
        $this->client->request(
            'GET',
            '/node/collection'
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        $rawResult = $this->client->getResponse()->getContent();

        $this->assertBpiMissingAuthentication($rawResult);
    }

    /**
     *
     */
    public function testNodeCollection() {
        $this->client->request(
            'GET',
            '/node/collection',
            [],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);

        // Assert 'item' tags with 'entity' type.
        /** @var \SimpleXMLElement[] $entityItemTags */
        $entityItemTags = $xml->xpath('//item[@type="entity"]');

        // Defaults to 10 items.
        $this->assertCount(RestController::NODE_LIST_AMOUNT, $entityItemTags);

        /** @var \SimpleXMLElement $entityItemTag */
        foreach ($entityItemTags as $entityItemTag) {
            $this->assertBpiEntity($entityItemTag);
        }

        // Assert 'item' tags with 'facet' type.
        $facetItemTags = $xml->xpath('//item[@type="facet"]');
        $this->assertCount(count(self::VALID_NODE_FACETS), $facetItemTags);

        foreach (self::VALID_NODE_FACETS as $facet) {
            /** @var \SimpleXMLElement[] $facetItemTag */
            $facetItemTag = $xml->xpath('//item[@type="facet" and @name="'.$facet.'"]');
            $this->assertCount(1, $facetItemTag);

            // Assert 'property' tags.
            /** @var \SimpleXMLElement[] $facetPropertyTags */
            $facetPropertyTags = $facetItemTag[0]->xpath('properties/property');
            $this->assertNotEmpty($facetPropertyTags);
            $this->assertGreaterThan(0, count($facetPropertyTags));

            /** @var \SimpleXMLElement $facetPropertyTag */
            foreach ($facetPropertyTags as $facetPropertyTag) {
                // Assert 'property' tags attributes and values.
                $this->assertNotNull($facetPropertyTag->attributes()['type']);
                $this->assertEquals('string', $facetPropertyTag->attributes()['type']);

                $this->assertNotNull($facetPropertyTag->attributes()['name']);
                $this->assertNotEmpty($facetPropertyTag->attributes()['name']);

                $this->assertGreaterThan(0, (int)$facetPropertyTag);
            }
        }

        // Assert 'item' tags with 'collection' type.
        $collectionItemTags = $xml->xpath('//item[@type="collection"]');
        $this->assertCount(1, $collectionItemTags);

        // Assert total number of nodes.
        /** @var \SimpleXMLElement[] $totalPropertyTag */
        $totalPropertyTag = $collectionItemTags[0]->xpath('properties/property[@name="total"]');
        $this->assertCount(1, $totalPropertyTag);

        // Assert total number of nodes.
        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $nodeRepository */
        $nodeRepository = $this->registry->getRepository(Node::class);
        /** @var Node[] $nodes */
        $nodes = $nodeRepository->findAll();
        $this->assertEquals(count($nodes), (int)$totalPropertyTag[0]);
    }

    /**
     *
     */
    public function testNodeCollectionWithAmount()
    {
        // Max value should be LESS than fixture generated lowest amount.
        $randomCount = mt_rand(5, NodeFixtures::NODE_COUNT_MIN);
        $this->client->request(
            'GET',
            '/node/collection',
            [
                'amount' => $randomCount,
            ],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);

        // Assert 'item' tags with 'entity' type.
        /** @var \SimpleXMLElement[] $entityItemTags */
        $entityItemTags = $xml->xpath('//item[@type="entity"]');
        $this->assertCount($randomCount, $entityItemTags);
    }

    /**
     *
     */
    public function testNodeCollectionPaged()
    {
        $numberOfNodes = $offset = 0;
        $nodesFetched = [];

        do {
            $randomCount = mt_rand(1, 6);
            $this->client->request(
                'GET',
                '/node/collection',
                [
                    'amount' => $randomCount,
                    'offset' => $offset,
                ],
                [],
                [
                    'HTTP_Auth' => $this->authenticationToken,
                ]
            );

            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

            $rawResponse = $this->client->getResponse()->getContent();
            $xml = new \SimpleXMLElement($rawResponse);

            // Assert 'item' tags with 'entity' type.
            /** @var \SimpleXMLElement[] $entityItemTags */
            $entityItemTags = $xml->xpath('//item[@type="entity"]');
            $lastEntityCount = count($entityItemTags);

            /** @var \SimpleXMLElement $entityItemTag */
            foreach ($entityItemTags as $entityItemTag) {
                $nodeIdProperty = $entityItemTag->xpath('properties/property[@name="id"]');
                $this->assertCount(1, $nodeIdProperty);
                $nodesFetched[] = (string)$nodeIdProperty[0];
            }

            $offset += $randomCount;
            $numberOfNodes += $lastEntityCount;
        }
        while(0 !== $lastEntityCount);

        // Assert total number of nodes.
        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $nodeRepository */
        $nodeRepository = $this->registry->getRepository(Node::class);
        /** @var Node[] $nodes */
        $nodes = $nodeRepository->findAll();
        $this->assertEquals(count($nodes), $numberOfNodes);
        // Make sure a unique set of nodes fetched, meaning that loop paginated
        // correctly.
        $this->assertCount(count($nodes), array_unique($nodesFetched));
    }

    /**
     *
     */
    function testNodeCollectionSortedByTitleAscending() {
        $this->client->request(
            'GET',
            '/node/collection',
            [
                'sort' => ['title' => 'asc'],
            ],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);

        // Assert 'item' tags with 'entity' type.
        /** @var \SimpleXMLElement[] $entityItemTags */
        $entityItemTags = $xml->xpath('//item[@type="entity"]');

        $this->assertNotEmpty($entityItemTags);

        // Compare each title against the previous one.
        /** @var \SimpleXMLElement $entityItemTag */
        $previousTitle = '';
        foreach ($entityItemTags as $entityItemTag) {
            /** @var \SimpleXMLElement[] $titleProperty */
            $titleProperty = $entityItemTag->xpath('properties/property[@name="title"]');
            $this->assertNotEmpty($titleProperty);
            $currentTitle = (string) $titleProperty[0];
            // Ascending order means that two compared strings deliver
            // a negative value when.
            $this->assertLessThanOrEqual(0, strcmp($previousTitle, $currentTitle));
            $previousTitle = $currentTitle;
        }
    }

    /**
     *
     */
    function testNodeCollectionSortedByPushedDescending()
    {
        $this->client->request(
            'GET',
            '/node/collection',
            [
                'sort' => ['pushed' => 'desc'],
            ],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);

        // Assert 'item' tags with 'entity' type.
        /** @var \SimpleXMLElement[] $entityItemTags */
        $entityItemTags = $xml->xpath('//item[@type="entity"]');

        $this->assertNotEmpty($entityItemTags);

        // Compare each title against the previous one.
        /** @var \SimpleXMLElement $entityItemTag */
        $previousDate = strtotime('2099-01-01');
        foreach ($entityItemTags as $entityItemTag) {
            /** @var \SimpleXMLElement[] $pushedProperty */
            $pushedProperty = $entityItemTag->xpath('properties/property[@name="pushed"]');
            $this->assertNotEmpty($pushedProperty);
            $currentDate = strtotime((string) $pushedProperty[0]);
            $this->assertLessThanOrEqual($previousDate, $currentDate);
            $previousDate = $currentDate;
        }
    }

    /**
     * TODO: This test is pretty limited, extend when possible.
     * TODO: Add logicalOperator test.
     * TODO: Add multiple filtering test.
     */
    function testNodeCollectionFiltered()
    {
        $this->client->request(
            'GET',
            '/node/collection',
            [],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $initialXml = new \SimpleXMLElement($rawResponse);

        // Pick a random tag to filter on.
        /** @var \SimpleXMLElement[] $collectionTags */
        $collectionTags = $initialXml->xpath('//item[@type="facet" and @name="tags"]/properties/property');
        $this->assertNotEmpty($collectionTags);
        /** @var \SimpleXMLElement $randomTag */
        $randomTag = $collectionTags[mt_rand(0, count($collectionTags) - 1)];
        // The tag value.
        $randomTagValue = (string)$randomTag->attributes()['name'];
        // Number of entities containing this tag.
        $entitiesTaggedCount = (int)$randomTag;

        // Send a new, filtered request.
        $this->client->request(
            'GET',
            '/node/collection',
            [
                'filter' => [
                    'tags' => [
                        $randomTagValue,
                    ],
                ],
                'amount' => 99, // Make sure to grab all filtered nodes.
            ],
            [],
            [
                'HTTP_Auth' => $this->authenticationToken,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        /** @var \SimpleXMLElement $filteredXml */
        $filteredXml = new \SimpleXMLElement($rawResponse);

        // Tag facet value must match the number of filtered nodes.
        /** @var \SimpleXMLElement[] $filteredEntities */
        $filteredEntities = $filteredXml->xpath('//item[@type="entity"]');
        $this->assertCount($entitiesTaggedCount, $filteredEntities);

        // Check that entities actually contain the filtered tag value.
        /** @var \SimpleXMLElement $filteredEntity */
        foreach ($filteredEntities as $filteredEntity) {
            /** @var \SimpleXMLElement[] $tags */
            $tags = $filteredEntity->xpath('tags/tag[@tag_name="'.$randomTagValue.'"]');
            $this->assertNotEmpty($tags);
        }

        // The count of facets also should decrease, in comparison to unfiltered
        // query, showing only facets available for the filtered result.
        // Check number of 'type' facets for this logic.
        /** @var \SimpleXMLElement[] $initialTypeFacets */
        $initialTypeFacets = $initialXml->xpath('//item[@type="facet" and @name="type"]/properties/property');
        $filteredTypeFacets = $filteredXml->xpath('//item[@type="facet" and @name="type"]/properties/property');
        $this->assertLessThan(count($initialTypeFacets), count($filteredTypeFacets));
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            new AgencyFixtures(),
            new AudienceFixtures(),
            new CategoryFixtures(),
            new NodeFixtures(),
        ];
    }
}

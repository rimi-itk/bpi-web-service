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
    protected $requestToken;

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
        $this->requestToken = password_hash($agency->getAgencyId()->id().$agency->getPublicKey().$agency->getSecret(), PASSWORD_BCRYPT);
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
                'HTTP_Auth' => 'BPI agency="999999", token="'.$this->requestToken.'"',
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
                'HTTP_Auth' => 'BPI agency="999999", token="'.$this->requestToken.'"',
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
                'HTTP_Auth' => 'BPI agency="999999", token="'.$this->requestToken.'"',
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
        $randomCount = mt_rand(2, 22);
        $this->client->request(
            'GET',
            '/node/collection',
            [
                'amount' => $randomCount,
            ],
            [],
            [
                'HTTP_Auth' => 'BPI agency="999999", token="'.$this->requestToken.'"',
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
                    'HTTP_Auth' => 'BPI agency="999999", token="'.$this->requestToken.'"',
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

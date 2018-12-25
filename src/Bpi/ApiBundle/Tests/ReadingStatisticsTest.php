<?php

namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\DataFixtures\MongoDB\HistoryFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\NodeFixtures;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;

/**
 * Class ReadingStatisticsTest.
 */
class ReadingStatisticsTest extends AbstractFixtureAwareBpiTest
{
    /**
     *
     */
    public function testStatisticsAnonymous()
    {
        $this->client->request(
            'GET',
            '/statistics',
            [],
            [],
            []
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        $rawResult = $this->client->getResponse()->getContent();

        $this->assertBpiMissingAuthentication($rawResult);
    }

    /**
     *
     */
    public function testStatisticsWrongAuthentication()
    {
        $this->client->request(
            'GET',
            '/statistics',
            [],
            [],
            [
                'HTTP_Auth' => $this->generateAuthenticationHeader(new Agency()),
            ]
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testStatistics()
    {
        $ownerAgencyEntity = $this->registry->getRepository(Agency::class)
            ->findOneBy(
                [
                    'public_id' => '999999',
                ]
            );
        $this->assertNotEmpty($ownerAgencyEntity);

        $authenticationHeader = $this->generateAuthenticationHeader($ownerAgencyEntity);
        $this->client->request(
            'GET',
            '/statistics',
            [],
            [],
            [
                'HTTP_Auth' => $authenticationHeader,
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        /** @var \SimpleXMLElement $xml */
        $xml = new \SimpleXMLElement($rawResponse);

        $this->assertEquals('bpi', (string) $xml->getName());

        /** @var \SimpleXMLElement[] $itemTags */
        $itemTags = $xml->xpath('//item[@type="entity" and @name="statistics"]');
        $this->assertCount(1, $itemTags);

        /** @var \SimpleXMLElement[] $propertyTags */
        $propertyTags = $itemTags[0]->xpath('properties/property');
        $this->assertCount(2, $propertyTags);

        $this->assertEquals('push', (string)$propertyTags[0]->attributes()['name']);
        $this->assertEquals('syndicate', (string)$propertyTags[1]->attributes()['name']);

        // Store initial push/syndicate values.
        $pushCount = (int)$propertyTags[0];
        $syndicationCount = (int)$propertyTags[1];

        $syndicatingAgencyEntity = $this->registry->getRepository(Agency::class)
            ->findOneBy(
                [
                    'public_id' => '111111',
                ]
            );
        $this->assertNotEmpty($syndicatingAgencyEntity);

        // Mark a node a syndicated, to check the counters.
        // Pick a node to syndicate.
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $nodeEntity */
        $nodeEntity = $this
            ->registry
            ->getRepository(Node::class)
            ->findOneBy(
                [
                    'author.agency_id' => $ownerAgencyEntity->getAgencyId()->id(),
                ]
            );
        $this->assertNotEmpty($nodeEntity);

        // Make a syndicate request.
        $this->client->request(
            'GET',
            '/node/syndicated',
            [
                'id' => $nodeEntity->getId(),
            ],
            [],
            [
                'HTTP_Auth' => $this->generateAuthenticationHeader($syndicatingAgencyEntity),
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Check the counters.
        $this->client->request(
            'GET',
            '/statistics',
            [],
            [],
            [
                'HTTP_Auth' => $this->generateAuthenticationHeader($syndicatingAgencyEntity),
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        /** @var \SimpleXMLElement $xml */
        $xml = new \SimpleXMLElement($rawResponse);

        $syndicatePropertyTags = $xml->xpath('//item[@type="entity" and @name="statistics"]/properties/property[@name="syndicate"]');
        $this->assertCount(1, $syndicatePropertyTags);

        $syndicationCountUpdate = (int) $syndicatePropertyTags[0];
        $this->assertGreaterThan($syndicationCount, $syndicationCountUpdate);
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            new NodeFixtures(),
            new HistoryFixtures(),
        ];
    }
}

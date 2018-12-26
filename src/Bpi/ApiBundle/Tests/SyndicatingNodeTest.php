<?php

namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\DataFixtures\MongoDB\NodeFixtures;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;

/**
 * Class SyndicatingNodeTest.
 */
class SyndicatingNodeTest extends AbstractFixtureAwareBpiTest
{
    /**
     *
     */
    public function testMarkSyndicatedAnonymous()
    {
        $this->client->request(
            'GET',
            '/node/syndicated',
            [
                'id' => 1,
            ],
            [],
            []
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        $rawResult = $this->client->getResponse()->getContent();

        $this->assertBpiMissingAuthentication($rawResult);
    }

    public function testMarkSyndicatedWrongAuthentication()
    {
        $this->client->request(
            'GET',
            '/node/syndicated',
            [
                'id' => 1,
            ],
            [],
            [
                'HTTP_Auth' => $this->generateAuthenticationHeader(new Agency()),
            ]
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testMarkSyndicated()
    {
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Agency $ownerAgencyEntity */
        $ownerAgencyEntity = $this
            ->registry
            ->getRepository(Agency::class)
            ->findOneBy(
                [
                    'public_id' => '999999',
                ]
            );

        /** @var \Bpi\ApiBundle\Domain\Aggregate\Agency $syndicatingAgencyEntity */
        $syndicatingAgencyEntity = $this
            ->registry
            ->getRepository(Agency::class)
            ->findOneBy(
                [
                    'public_id' => '111111',
                ]
            );

        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $nodeEntity */
        $nodeEntity = $this
            ->registry
            ->getRepository(Node::class)
            ->findOneBy(
                [
                    'author.agency_id' => $ownerAgencyEntity->getAgencyId()->id(),
                ]
            );

        // Check that there are no syndications so far.
        $syndicationsCount = (int)$nodeEntity->getSyndications();
        $this->assertEquals(0, $syndicationsCount);

        // And this is reflected in the API response as well.
        $this->client->request(
            'GET',
            '/node/item/'.$nodeEntity->getId(),
            [],
            [],
            [
                'HTTP_Auth' => $this->generateAuthenticationHeader($ownerAgencyEntity),
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);
        /** @var \SimpleXMLElement[] $syndicationsProperty */
        $syndicationsProperty = $xml->xpath('//item[@type="entity"]/properties/property[@name="syndications"]');
        $this->assertNotEmpty($syndicationsProperty);
        $this->assertEquals(0, (int)$syndicationsProperty[0]);

        // Now, assuming that this node was syndicated, let know the API
        // about it.
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

        // Check the storage (reload the entity).
        $this->registry->getManager()->refresh($nodeEntity);

        $this->assertEquals(1, (int)$nodeEntity->getSyndications());

        // Check the API response for same value.
        $this->client->request(
            'GET',
            '/node/item/'.$nodeEntity->getId(),
            [],
            [],
            [
                'HTTP_Auth' => $this->generateAuthenticationHeader($ownerAgencyEntity),
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);

        /** @var \SimpleXMLElement[] $syndicationsProperty */
        $syndicationsProperty = $xml->xpath('//item[@type="entity"]/properties/property[@name="syndications"]');
        $this->assertNotEmpty($syndicationsProperty);
        $this->assertEquals(1, (int)$syndicationsProperty[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return [
            new NodeFixtures(),
        ];
    }
}

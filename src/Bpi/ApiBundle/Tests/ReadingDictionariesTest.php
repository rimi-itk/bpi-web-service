<?php

namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\DataFixtures\MongoDB\AgencyFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\AudienceFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\CategoryFixtures;
use Bpi\ApiBundle\Domain\Aggregate\Agency;

class ReadingDictionariesTest extends AbstractFixtureAwareBpiTest
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
    public function testAnonymousDictionaries()
    {
        $this->client->request(
            'GET',
            '/profile/dictionary'
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        $rawResult = $this->client->getResponse()->getContent();

        $this->assertBpiMissingAuthentication($rawResult);
    }

    /**
     *
     */
    public function testDictionaries()
    {
        $this->client->request(
            'GET',
            '/profile/dictionary',
            [],
            [],
            [
                'HTTP_Auth' => 'BPI agency="999999", token="'.$this->requestToken.'"',
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $xml = new \SimpleXMLElement($this->client->getResponse()->getContent());

        // Assert root.
        $this->assertEquals('bpi', $xml->getName());
        $this->assertNotNull($xml->attributes()['version']);

        // Assert 'item' tags.
        /** @var \SimpleXMLElement[] $item */
        $itemTags = $xml->xpath('item');
        $this->assertNotEmpty($itemTags);

        // Assert each 'item' tag structure.
        /** @var \SimpleXMLElement $itemTag */
        foreach ($itemTags as $itemTag) {
            $this->assertNotNull($itemTag->attributes()['type']);
            $this->assertNotEmpty((string)$itemTag->attributes()['type']);

            $this->assertNotNull($itemTag->attributes()['name']);
            $this->assertNotEmpty((string)$itemTag->attributes()['name']);

            // Assert 'properties' tag.
            /** @var \SimpleXMLElement[] $propertiesTags */
            $propertiesTags = $itemTag->xpath('properties');
            $this->assertNotEmpty($propertiesTags);
            $this->assertCount(1, $propertiesTags);

            // Assert 'property' tag.
            /** @var \SimpleXMLElement[] $propertyTags */
            $propertyTags = $propertiesTags[0]->xpath('property');
            $this->assertNotEmpty($propertyTags);
            $this->assertCount(2, $propertyTags);

            // Assert each 'property' tag structure.
            /** @var \SimpleXMLElement $propertyTag */
            foreach ($propertyTags as $propertyTag) {
                $this->assertNotNull($propertyTag->attributes()['type']);
                $this->assertNotNull($propertyTag->attributes()['name']);
                $this->assertNotNull($propertyTag->attributes()['title']);
                $this->assertNotEmpty((string)$propertyTag);
            }
        }
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
        ];
    }
}

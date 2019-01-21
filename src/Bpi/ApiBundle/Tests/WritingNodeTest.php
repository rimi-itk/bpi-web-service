<?php

namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\DataFixtures\MongoDB\AgencyFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\AudienceFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\CategoryFixtures;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Entity\Tag;
use Faker\Factory;

class WritingNodeTest extends AbstractFixtureAwareBpiTest
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    private $cleanupAssets = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     *
     */
    public function testPushAnonymous()
    {
        $this->client->request(
            'POST',
            '/node',
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
    public function testPushWrongAuthentication()
    {
        $this->client->request(
            'POST',
            '/node',
            [],
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
    public function testPush()
    {
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Agency $agency */
        $agency = $this->registry->getRepository(Agency::class)
            ->findOneBy(
                [
                    'public_id' => '999999',
                ]
            );
        $this->assertNotEmpty($agency);

        $tags = ['alpha', 'beta', 'gamma', 'pi', 'rho'];

        $payload = [
            'agency_id' => $agency->getPublicId(),
            'local_id' => mt_rand(1, 9999999),
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'title' => $this->faker->sentence,
            'body' => implode("\n", $this->faker->paragraphs),
            'teaser' => $this->faker->paragraph,
            'creation' => $this->faker->date(DATE_W3C),
            'type' => $this->faker->name,
            'category' => 'Litteratur',
            'audience' => 'Voksne',
            'tags' => implode(',', $tags),
            'assets' => [
                [
                    'path' => 'https://picsum.photos/200',
                    'name' => $this->faker->sentence,
                    'title' => $this->faker->sentence,
                    'alt' => $this->faker->sentence,
                    'extension' => 'jpeg',
                    'type' => $this->faker->sentence,
                    'width' => 200,
                    'height' => 200,
                ],
            ],
        ];

        $this->client->request(
            'POST',
            '/node',
            $payload,
            [],
            [
                'HTTP_Auth' => $this->generateAuthenticationHeader($agency),
            ]
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $rawResponse = $this->client->getResponse()->getContent();
        $xml = new \SimpleXMLElement($rawResponse);

        /** @var \SimpleXMLElement[] $entityTags */
        $entityTags = $xml->xpath('//item[@type="entity"]');
        $this->assertCount(1, $entityTags);

        /** @var \SimpleXMLElement[] $idProperties */
        $idProperties = $entityTags[0]->xpath('properties/property[@name="id"]');
        $this->assertCount(1, $idProperties);
        $entityId = (string) $idProperties[0];

        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $nodeEntity */
        $nodeEntity = $this->registry->getRepository(Node::class)->find($entityId);
        $this->assertNotEmpty($nodeEntity);
        $this->assertEquals($payload['agency_id'], $nodeEntity->getAuthor()->getAgencyId()->id());
        $this->assertEquals($payload['firstname'], $nodeEntity->getAuthorFirstName());
        $this->assertEquals($payload['lastname'], $nodeEntity->getAuthorLastName());
        $this->assertEquals($payload['title'], $nodeEntity->getTitle());
        $this->assertEquals($payload['body'], $nodeEntity->getBody());
        $this->assertEquals($payload['teaser'], $nodeEntity->getTeaser());
        $this->assertNotEmpty(strtotime($nodeEntity->getCtime()->format(DATE_W3C)));
        $this->assertEquals($payload['type'], $nodeEntity->getType());
        $this->assertEquals($payload['category'], $nodeEntity->getCategory()->getCategory());
        $this->assertEquals($payload['audience'], $nodeEntity->getAudience()->getAudience());

        /** @var Tag[] $nodeEntityTags */
        $nodeEntityTags = $nodeEntity->getTags();
        foreach ($nodeEntityTags as $nodeEntityTag) {
            $this->assertTrue(in_array($nodeEntityTag->getTag(), $tags));
        }
        $this->assertCount(count($tags), $nodeEntityTags);

        // Check asset upload.
        $this->assertCount(1, $nodeEntity->getAssets()->getCollection());
        // TODO: Take path from config.
        $uploadsPath = $this->container->getParameter('kernel.project_dir').'/web/uploads/assets';
        /** @var \Bpi\ApiBundle\Domain\Entity\File $asset */
        foreach ($nodeEntity->getAssets()->getCollection() as $asset) {
            $fileName = $uploadsPath.'/'.$asset->getName().'.'.$asset->getExtension();
            $this->cleanupAssets[] = $fileName;
            $this->assertFileExists($fileName);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        foreach ($this->cleanupAssets as $fileName) {
            if (file_exists($fileName)) {
                unlink($fileName);
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

<?php

namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\DataFixtures\MongoDB\AgencyFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\AudienceFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\CategoryFixtures;
use Bpi\ApiBundle\DataFixtures\MongoDB\NodeFixtures;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;

/**
 * Class ReadingTest.
 */
class ReadingTest extends AbstractFixtureAwareBpiTest
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

        /** @var Agency $agency */
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
    public function testWithMissingAuthentication()
    {
        $this->client->request('GET', '/node/item/123456');

        $rawResult = $this->client->getResponse()->getContent();

        $xml = simplexml_load_string($rawResult);
        $this->assertNotFalse($xml);
        $this->assertEquals('SimpleXMLElement', get_class($xml));

        $expectedXml = '<result><![CDATA[Authorization required (none)]]></result>';
        $this->assertXmlStringEqualsXmlString($expectedXml, $xml->asXML());

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testMissingNodeById()
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
    public function testFetchNodeById()
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
    }

    /**
     *
     */
    public function testFetchDictionaries()
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

        $expectedXml = '<bpi version="3">
  <item type="audience" name="noname">
    <properties>
      <property type="string" name="group" title=""><![CDATA[audience]]></property>
      <property type="string" name="name" title=""><![CDATA[Studerende]]></property>
    </properties>
    <assets/>
  </item>
  <item type="audience" name="noname">
    <properties>
      <property type="string" name="group" title=""><![CDATA[audience]]></property>
      <property type="string" name="name" title=""><![CDATA[Voksne]]></property>
    </properties>
    <assets/>
  </item>
  <item type="category" name="noname">
    <properties>
      <property type="string" name="group" title=""><![CDATA[category]]></property>
      <property type="string" name="name" title=""><![CDATA[Litteratur]]></property>
    </properties>
    <assets/>
  </item>
  <item type="category" name="noname">
    <properties>
      <property type="string" name="group" title=""><![CDATA[category]]></property>
      <property type="string" name="name" title=""><![CDATA[Sport]]></property>
    </properties>
    <assets/>
  </item>
</bpi>';

        $this->assertXmlStringEqualsXmlString($expectedXml, $this->client->getResponse()->getContent());
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

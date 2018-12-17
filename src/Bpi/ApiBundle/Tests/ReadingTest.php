<?php

namespace Bpi\ApiBundle\Tests;

use Bpi\ApiBundle\DataFixtures\MongoDB\NodeFixtures;
use Bpi\ApiBundle\Domain\Aggregate\Node;

/**
 * Class ReadingTest.
 */
class ReadingTest extends AbstractFixtureAwareBpiTest
{
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
        $this->assertXmlStringEqualsXmlString(
            '<result><![CDATA[Authorization required (none)]]></result>',
            $xml->asXML()
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     *
     */
    public function testMissingNodeById()
    {
        $this->client->request('GET', '/node/item/123456');

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
        $nodeId = $nodes[0]->getId();

        $this->client->request('GET', '/node/item/'.$nodeId);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
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

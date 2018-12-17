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
    public function testFetchNodeById()
    {
        $nodeRepository = $this->dm->getRepository(Node::class);

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

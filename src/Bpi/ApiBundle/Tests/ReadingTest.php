<?php

namespace Bpi\ApiBundle\Tests;

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
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}

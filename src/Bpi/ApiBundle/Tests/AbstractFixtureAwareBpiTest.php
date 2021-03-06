<?php

namespace Bpi\ApiBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class AbstractFixtureAwareBpiTest extends AbstractBaseBpiTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $loader = new Loader();
        /** @var \Doctrine\Bundle\FixturesBundle\Fixture $fixture */
        foreach ($this->getFixtures() as $fixture) {
            if ($fixture instanceof ContainerAwareInterface) {
                $fixture->setContainer($this->container);
            }
            $loader->addFixture($fixture);
        }
        $purger = new MongoDBPurger($this->registry->getManager());
        $executor = new MongoDBExecutor($this->registry->getManager(), $purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Gets an array of fixtures to be managed.
     *
     * @return array
     */
    abstract public function getFixtures();
}

<?php
namespace Bpi\ApiBundle\Tests\Service\Fixtures\Other;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\DataFixtures\MongoDB\FakeData;

class LoadProfile extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        FakeData::createCategories($manager);
        FakeData::createAudiences($manager);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}

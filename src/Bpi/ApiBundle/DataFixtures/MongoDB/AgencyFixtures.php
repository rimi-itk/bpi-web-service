<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

/**
 * Class AgencyFixtures.
 */
class AgencyFixtures extends Fixture
{

    const TEST_AGENCY = 'agency-test_agency';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = FakerFactory::create();

        $testAgency = new Agency();
        $testAgency->setModerator($faker->name);
        $testAgency->setInternal(true);
        $testAgency->setPublicId(999999);
        $testAgency->setPublicKey('3fa');
        $testAgency->setSecret('abc');

        $manager->persist($testAgency);
        $manager->flush();

        $this->addReference(self::TEST_AGENCY, $testAgency);
    }
}

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

    const AGENCY_999999 = 'agency-999999';
    const AGENCY_111111 = 'agency-111111';

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
        $testAgency->setName($faker->sentence);

        $manager->persist($testAgency);
        $manager->flush();

        $this->addReference(self::AGENCY_999999, $testAgency);

        $testAgency = new Agency();
        $testAgency->setModerator($faker->name);
        $testAgency->setInternal(true);
        $testAgency->setPublicId(111111);
        $testAgency->setPublicKey('3fb');
        $testAgency->setSecret('abd');
        $testAgency->setName($faker->sentence);

        $manager->persist($testAgency);
        $manager->flush();

        $this->addReference(self::AGENCY_111111, $testAgency);
    }
}

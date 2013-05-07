<?php

namespace Bpi\ApiBundle\Tests\DoctrineFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Agency;

class LoadAgencies implements FixtureInterface
{
    const AGENCY_ALPHA = '200100';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Agency(self::AGENCY_ALPHA, 'Aarhus Kommunes Biblioteker', 'Agency Moderator Name', md5('agency_200100_public'), sha1('agency_200100_secret')));
        $manager->flush();
    }

}

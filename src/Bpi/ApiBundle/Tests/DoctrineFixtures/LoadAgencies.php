<?php

namespace Bpi\ApiBundle\Tests\DoctrineFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Agency;

class LoadAgencies implements FixtureInterface
{
    const AGENCY_ALPHA = '100200';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Agency(self::AGENCY_ALPHA, 'Aarhus Kommunes Biblioteker', 'Moderator Arhus', 'arhus_public_key', 'secret'));
        $manager->flush();
    }

}

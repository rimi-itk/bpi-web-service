<?php

namespace Bpi\ApiBundle\Tests\DoctrineFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Agency;

class LoadAgencies implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Agency('Agency Alpha', 'Moderator Alpha', 'alpha_public_key', 'secret'));
        $manager->persist(new Agency('Agency Bravo', 'Moderator Bravo', 'bravo_public_key', 'secret'));
        $manager->persist(new Agency('Agency Charlie', 'Moderator Charlie', 'charlie_public_key', 'secret'));
        $manager->flush();
    }

}

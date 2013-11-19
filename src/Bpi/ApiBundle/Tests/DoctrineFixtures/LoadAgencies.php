<?php

namespace Bpi\ApiBundle\Tests\DoctrineFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Agency;

class LoadAgencies extends AbstractFixture implements OrderedFixtureInterface
{
    const AGENCY_ALPHA = '200100';
    const AGENCY_BRAVO = '200200';
    const AGENCY_BRAVO_SECRET = '343b7074f929cad1b96f16558b938ec9695c7ecb'; //sha1('agency_200200_secret');
    const AGENCY_BRAVO_KEY = '7b4a28991ac893914905b525614abd9e'; //md5('agency_200200_public');

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Agency(self::AGENCY_ALPHA, 'Aarhus Kommunes Biblioteker', 'Agency Moderator Name', md5('agency_200100_public'), sha1('agency_200100_secret')));
        $manager->persist(new Agency(self::AGENCY_BRAVO, 'Bbbb Kommunes Biblioteker', 'Bravo Agency Moderator Name', self::AGENCY_BRAVO_KEY, self::AGENCY_BRAVO_SECRET));
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}

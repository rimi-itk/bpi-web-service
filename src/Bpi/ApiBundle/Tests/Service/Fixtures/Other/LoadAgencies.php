<?php
namespace Bpi\ApiBundle\Tests\Service\Fixtures\Other;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Agency as Agency;

class LoadAgencies extends AbstractFixture implements OrderedFixtureInterface
{
    const AGENCY_ALPHA = '200100';
    const AGENCY_BRAVO = '200200';
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist(new Agency(self::AGENCY_ALPHA, 'Aarhus Kommunes Biblioteker', 'Agency Moderator Name', md5('agency_200100_public'), sha1('agency_200100_secret')));
        $manager->persist(new Agency(self::AGENCY_BRAVO, 'Bbbb Kommunes Biblioteker', 'Bravo Agency Moderator Name', md5('agency_200200_public'), sha1('agency_200200_secret')));
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

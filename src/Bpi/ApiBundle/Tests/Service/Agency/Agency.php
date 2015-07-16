<?php
namespace Bpi\ApiBundle\Tests\Service\Agency;

use Bpi\ApiBundle\Tests\Service\BpiTest as BpiTest;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadAgencies;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadNodes;

class Agency extends BpiTest
{

    public function setUp()
    {
        parent::setUp();

        $this->console = new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel);
        $this->console->setAutoExit(false);
        $this->loadFixtures = new \Symfony\Component\Console\Input\ArrayInput(
            array(
                "--env" => "test",
                "--quiet" => true,
                "--append" => true,
                "--fixtures" => 'src/Bpi/ApiBundle/Tests/Service/Fixtures/Other',
                'command' => 'doctrine:mongodb:fixtures:load'
            )
        );
        $this->console->run($this->loadFixtures);

        $this->loadFixtures = new \Symfony\Component\Console\Input\ArrayInput(
            array(
                "--env" => "test",
                "--quiet" => true,
                "--append" => true,
                "--fixtures" => 'src/Bpi/ApiBundle/Tests/Service/Fixtures/Other',
                'command' => 'doctrine:mongodb:fixtures:load'
            )
        );

        $this->console->run($this->loadFixtures);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {

        $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Node')
            ->remove()
            ->field('author.agency_id')
            ->in(array(LoadAgencies::AGENCY_ALPHA, LoadAgencies::AGENCY_BRAVO))
            ->getQuery()
            ->execute();
        $this->em->flush();

        $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Entity\History')
            ->remove()
            ->field('agency')
            ->in(array(LoadAgencies::AGENCY_ALPHA, LoadAgencies::AGENCY_BRAVO))
            ->getQuery()
            ->execute();
        $this->em->flush();

        parent::tearDown();
    }

    public function testAgency()
    {
        $agency = $this->em->createQueryBuilder('BpiApiBundle:Aggregate\Agency')
            ->field('public_id')
            ->equals('200100')
            ->getQuery()
            ->getSingleResult()
        ;

        $this->assertNotNull($agency, 'Agency not found in DB.');

        $agencyParameters = array(
            'publicId' => $agency->getPublicId(),
            'name' => $agency->getName(),
            'internal' => $agency->getInternal(),
            'moderator' => $agency->getModerator()
        );

        $this->assertEquals($agencyParameters['publicId'], '200100', 'Agency Id not equal to 200100.');
        $this->assertEquals($agencyParameters['name'], 'Aarhus Kommunes Biblioteker', 'Agency 200100 name mismatch.');
        $this->assertEquals($agencyParameters['internal'], true, 'Agency 200100 should be internal.');
        $this->assertEquals($agencyParameters['moderator'], 'Agency Moderator Name', 'Agency 200100 moderator name not equal.');

        $agency = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Agency')
            ->field('public_id')
            ->equals('200200')
            ->getQuery()
            ->getSingleResult()
        ;

        $agencyParameters = array(
            'publicId' => $agency->getPublicId(),
            'name' => $agency->getName(),
            'internal' => $agency->getInternal(),
            'moderator' => $agency->getModerator()
        );

        $this->assertNotNull($agency, 'Agency not found in DB.');
        $this->assertEquals($agencyParameters['publicId'], '200200', 'Agency Id not equal to 200200.');
        $this->assertEquals($agencyParameters['name'], 'Bbbb Kommunes Biblioteker', 'Agency 200200 name mismatch.');
        $this->assertEquals($agencyParameters['internal'], false, 'Agency 200200 should be external.');
        $this->assertEquals($agencyParameters['moderator'], 'Bravo Agency Moderator Name', 'Agency 200200 moderator name not equal.');
    }
}

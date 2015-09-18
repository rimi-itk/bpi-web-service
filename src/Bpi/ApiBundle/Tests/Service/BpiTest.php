<?php
namespace Bpi\ApiBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadAgencies;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadProfile;

abstract class BpiTest extends WebTestCase
{
    protected $console;
    protected $loadFixtures;

    protected $em;
    protected $container;
    protected $domain;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->container = static::$kernel->getContainer();
        $this->em = $this->container->get('doctrine_mongodb')->getManager();
        $bpitestDomain = $this->container->getParameter('unit_test_domain');

        $this->domain = 'http://' . $bpitestDomain;

        $this->console = new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel);
        $this->console->setAutoExit(false);
        $this->loadFixtures = new \Symfony\Component\Console\Input\ArrayInput(array(
            "--env" => "test",
            "--quiet" => true,
            "--append" => true,
            "--fixtures" => 'src/Bpi/ApiBundle/Tests/Service/Fixtures/Other',
            'command' => 'doctrine:mongodb:fixtures:load'
        ));
        $this->console->run($this->loadFixtures);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $categories = array('Other','Event','Music','Facts','Book','Film','Literature','Themes','Markdays','Games','Campaigns');
        $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Entity\Category')
        ->remove()
        ->field('category')
        ->in($categories)
        ->getQuery()
        ->execute();
        $this->em->flush();

        $audiences = array('All','Adult','Kids','Young','Elders');
        $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Entity\Audience')
        ->remove()
        ->field('audience')
        ->in($audiences)
        ->getQuery()
        ->execute();
        $this->em->flush();

        $agencies = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Agency')
        ->remove()
        ->field('public_id')
        ->in(array(LoadAgencies::AGENCY_ALPHA, LoadAgencies::AGENCY_BRAVO))
        ->getQuery()
        ->execute();
        $this->em->flush();

        parent::tearDown();
    }

}

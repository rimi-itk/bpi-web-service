<?php
namespace Bpi\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTestCase extends WebTestCase
{
    protected $console;
    protected $load_fixtures;

    protected function loadFixture($name)
    {
        $fixture = file_get_contents(__DIR__.'/../Fixtures/'.$name.'.bpi');

        if ($name == 'Push')
            return $this->replaceAgencyIdWithActualValue($fixture);

        return $fixture;
    }

    protected function replaceAgencyIdWithActualValue($fixture)
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $dm = static::$kernel->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $query = $dm->getRepository('BpiApiBundle:Aggregate\Agency')->createQueryBuilder();
        $real_agency_id = $query->limit(1)->getQuery()->execute()->getSingleResult()->getAgencyId()->id();
        return str_replace('stub_agency_id', $real_agency_id, $fixture);
    }

    public function __construct()
    {
        $this->console = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->createKernel());
        $this->console->setAutoExit(false);
        $this->load_fixtures = new \Symfony\Component\Console\Input\ArrayInput(array(
            "--env" => "test",
            "--quiet" => true,
            "--fixtures" => 'src/Bpi/ApiBundle/Tests/DoctrineFixtures',
            'command' => 'doctrine:mongodb:fixtures:load'
        ));
        $this->console->run($this->load_fixtures);
    }

    public function doRequest($uri, $body, $method = 'POST')
    {
        $auth = new \Bpi\Sdk\Authorization('200100', md5('agency_200100_public'), sha1('agency_200100_secret'));
        $client = static::createClient();
        $headers = array(
            'HTTP_Content_Type' => 'application/vnd.bpi.api+xml',
            'HTTP_Auth' => $auth->toHTTPHeader(),
        );
        $client->request($method, $uri, array(), array(), $headers, $body);
        return $client;
    }
}

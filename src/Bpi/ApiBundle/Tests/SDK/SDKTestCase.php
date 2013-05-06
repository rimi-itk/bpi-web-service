<?php
namespace Bpi\ApiBundle\Tests\SDK;

require_once __DIR__ . '/../../../Sdk/Bpi/Sdk/Bpi.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\Sdk\Document;
use Bpi\Sdk\Authorization;

class SDKTestCase extends WebTestCase
{
    const TEST_ENDPOINT_URI = 'http://bpi.dev/app_dev.php/';
    protected $auth_agency;
    protected $auth_secret;
    protected $auth_pk;

    public function __construct()
    {
        $this->auth_agency = '200100';
        $this->auth_secret = sha1('agency_200100_secret');
        $this->auth_pk = md5('agency_200100_public');
        parent::__construct();
    }

    public function setUp()
    {
        $this->reloadFixtures();
        parent::setUp();
    }

    protected function reloadFixtures()
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

    protected function createDocument(\Goutte\Client $client)
    {
        return new Document($client, new Authorization($this->auth_agency, $this->auth_pk, $this->auth_secret));
    }
}

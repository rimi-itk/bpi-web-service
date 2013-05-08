<?php
namespace Bpi\ApiBundle\Tests\SDK;

require_once __DIR__ . '/../../../Sdk/Bpi/Sdk/Bpi.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\Sdk\Document;
use Bpi\Sdk\Authorization;
use Bpi\ApiBundle\Tests\DoctrineFixtures\LoadAgencies;

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

    /**
     *
     * @return \Bpi
     */
    public function createBpi()
    {
        return new \Bpi(self::TEST_ENDPOINT_URI, $this->auth_agency, $this->auth_pk, $this->auth_secret);
    }

    /**
     *
     * @return \Bpi
     */
    public function createBpiBravo()
    {
        return new \Bpi(self::TEST_ENDPOINT_URI, LoadAgencies::AGENCY_BRAVO, LoadAgencies::AGENCY_BRAVO_KEY, LoadAgencies::AGENCY_BRAVO_SECRET);
    }

    /**
     *
     * @return type
     */
    public function createRandomDataForPush()
    {
        $dt = new \DateTime();
        return array(
            'title' => 'title_' . mt_rand(),
            'body' => '<span title="zoo">body</span>_' . mt_rand(),
            'teaser' => 'teaser_' . mt_rand(),
            'type' => 'article',
            'creation' => $dt->format(\DateTime::W3C),
            'category' => 'category',
            'audience' => 'all',
            'editable' => 1,
            'authorship' => 1,
            // this value must exists, otherwise it will fail
            'agency_id' => $this->auth_agency,
            'local_id' =>  mt_rand(),
            'firstname' => 'firstname' . mt_rand(),
            'lastname' => 'lastname' . mt_rand(),
        );
    }

    public function getPredefinedLocalNode($id)
    {
        $dt = new \DateTime();
        $nodes = array('alpha' => array(
            'title' => 'title_alpha',
            'body' => '<span title="zoo">body</span> alpha',
            'teaser' => 'teaser_alpha',
            'type' => 'article',
            'creation' => $dt->format(\DateTime::W3C),
            'category' => 'category',
            'audience' => 'all',
            'editable' => 1,
            'authorship' => 1,
            // this value must exists, otherwise it will fail
            'agency_id' => $this->auth_agency,
            'local_id' =>  '12345',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
        ));

        return $nodes[$id];
    }
}

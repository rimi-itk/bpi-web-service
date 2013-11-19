<?php
namespace Bpi\ApiBundle\Tests\SDK;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\Sdk\Document;
use Bpi\Sdk\Authorization;
use Bpi\ApiBundle\Tests\DoctrineFixtures\LoadAgencies;

class SDKTestCase extends WebTestCase
{
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

    protected static function getEndpointUri()
    {
        $uri = getenv('BPI_TEST_ENDPOINT_URI');
        if ($uri === false) {
            throw new \RuntimeException('BPI_TEST_ENDPOINT_URI variable should be defined');
        }

        return $uri;
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
        return new \Bpi(self::getEndpointUri(), $this->auth_agency, $this->auth_pk, $this->auth_secret);
    }

    /**
     *
     * @return \Bpi
     */
    public function createBpiBravo()
    {
        return new \Bpi(self::getEndpointUri(), LoadAgencies::AGENCY_BRAVO, LoadAgencies::AGENCY_BRAVO_KEY, LoadAgencies::AGENCY_BRAVO_SECRET);
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
            'category' => 'Event',
            'audience' => 'Kids',
            'editable' => 1,
            'authorship' => 1,
            // this value must exists, otherwise it will fail
            'agency_id' => $this->auth_agency,
            'local_id' =>  mt_rand(),
            'firstname' => 'firstname' . mt_rand(),
            'lastname' => 'lastname' . mt_rand(),
            'images' => array(),
            'related_materials' => array('100200:12345678', '200100:22334455')
        );
    }

    public function getPredefinedLocalNode($id, $agency_id = null)
    {
        $dt = new \DateTime();
        $nodes = array('alpha' => array(
            'title' => 'title_alpha',
            'body' => '<span title="zoo">body</span> alpha',
            'teaser' => 'teaser_alpha',
            'type' => 'article',
            'creation' => $dt->format(\DateTime::W3C),
            'category' => 'Film',
            'audience' => 'All',
            'editable' => 1,
            'authorship' => 1,
            // this value must exists, otherwise it will fail
            'agency_id' => $agency_id ? $agency_id : $this->auth_agency,
            'local_id' =>  '12345',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'images' => array(),
            'related_materials' => array()
        ));

        return $nodes[$id];
    }
}

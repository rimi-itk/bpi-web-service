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

    protected function createDocument(\Goutte\Client $client)
    {
        return new Document($client, new Authorization($this->auth_agency, $this->auth_pk, $this->auth_secret));
    }
}

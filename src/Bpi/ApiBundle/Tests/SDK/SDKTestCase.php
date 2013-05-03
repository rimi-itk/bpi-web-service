<?php
namespace Bpi\ApiBundle\Tests\SDK;

require_once __DIR__ . '/../../../Sdk/Bpi/Sdk/Bpi.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\Sdk\Document;
use Bpi\Sdk\Authorization;

class SDKTestCase extends WebTestCase
{
    const TEST_ENDPOINT_URI = 'http://bpi.dev/app_dev.php/';

    protected function createDocument(\Goutte\Client $client)
    {
        return new Document($client, new Authorization(mt_rand(), mt_rand(), mt_rand()));
    }
}

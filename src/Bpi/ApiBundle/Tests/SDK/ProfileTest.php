<?php
namespace Bpi\ApiBundle\Tests\Controller;

require_once __DIR__ . '/../../../Sdk/vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\Sdk\Document;

class ProfileTest extends WebTestCase
{
    public function testDictionary()
    {
        $doc = new Document($client = new \Goutte\Client());
        $doc->request('GET', 'http://bpi.dev/app_dev.php/');
        $doc->firstItem('name', 'profile')->link('dictionary')->follow($doc);

        $this->assertTrue($doc->count() > 0, 'Length of items must be greater than zero');
    }
}

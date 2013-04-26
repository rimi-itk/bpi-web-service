<?php
namespace Bpi\ApiBundle\Tests\Controller;

require_once __DIR__ . '/../../../Sdk/vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\Sdk\Document;

class EndpointTest extends WebTestCase {

  public function testEndpoint() {
    $doc = new Document(new \Goutte\Client());
    $doc->request('GET', 'http://bpi.dev/app_dev.php/');

    $this->assertEquals(1, $doc->count());

    // @todo: $doc->filterBy('name', 'node')
  }
}
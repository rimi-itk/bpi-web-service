<?php
namespace Bpi\ApiBundle\Tests\Controller;

require_once __DIR__ . '/../../../Sdk/vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Bpi\Sdk\Document;

class EndpointTest extends WebTestCase
{
    public function testEndpoint()
    {
        $doc = new Document($client = new \Goutte\Client());
        $doc->request('GET', 'http://bpi.dev/app_dev.php/');

        $this->assertEquals(1, $doc->count());
        $doc->reduceItemsByAttr('name', 'node');
        $this->assertTrue($doc->link('self') instanceof \Bpi\Sdk\Link);
        $this->assertTrue($doc->link('collection') instanceof \Bpi\Sdk\Link);
        $this->assertTrue($doc->query('item') instanceof \Bpi\Sdk\Query);
        $this->assertTrue($doc->template('push') instanceof \Bpi\Sdk\Template);
    }
}

<?php
namespace Bpi\ApiBundle\Tests\SDK;

use Bpi\Sdk\Document;

class EndpointTest extends SDKTestCase
{
    public function testEndpoint()
    {
        $doc = $this->createDocument($client = new \Goutte\Client());
        $doc->loadEndpoint(self::TEST_ENDPOINT_URI);

        $doc->firstItem('name', 'node');
        $this->assertEquals(1, $doc->count());
        $this->assertTrue($doc->link('self') instanceof \Bpi\Sdk\Link);
        $this->assertTrue($doc->link('collection') instanceof \Bpi\Sdk\Link);
        $this->assertTrue($doc->query('item') instanceof \Bpi\Sdk\Query);
        $this->assertTrue($doc->template('push') instanceof \Bpi\Sdk\Template);
    }
}

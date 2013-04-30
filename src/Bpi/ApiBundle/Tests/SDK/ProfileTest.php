<?php
namespace Bpi\ApiBundle\Tests\SDK;

use Bpi\Sdk\Document;

class ProfileTest extends SDKTestCase
{
    public function testDictionary()
    {
        $doc = $this->createDocument($client = new \Goutte\Client());
        $doc->loadEndpoint(self::TEST_ENDPOINT_URI);
        $doc->firstItem('name', 'profile')->link('dictionary')->follow($doc);

        $this->assertTrue($doc->count() > 0, 'Length of items must be greater than zero');
    }
}

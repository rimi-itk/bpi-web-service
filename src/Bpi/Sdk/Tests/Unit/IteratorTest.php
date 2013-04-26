<?php
namespace Bpi\Sdk\Tests\Unit;

use Symfony\Component\DomCrawler\Crawler;
use Bpi\Sdk\Document;

class IteratorTest extends \PHPUnit_Framework_TestCase
{
    protected function createMockDocument($fixture)
    {
        $client = $this->getMock('Goutte\Client');
        $client->expects($this->at(0))
              ->method('request')
              ->will($this->returnValue(new Crawler(file_get_contents(__DIR__ . '/Fixtures/' . $fixture . '.bpi'))));
        
        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        return $doc;
    }

    public function testIterateOverSingleEntity()
    {
        $doc = $this->createMockDocument('Node');
        
        $this->assertEquals(1, $doc->count());
        $this->assertTrue($doc->isTypeOf('entity'));
    }
    
    public function testIterateOverMultipleEntities()
    {
        $doc = $this->createMockDocument('Collection');
        
        $this->assertEquals(2, $doc->count());
        
        $i = 0;
        foreach ($doc as $item)
        {
            if ($i == 0)
                $this->assertTrue($item->isTypeOf('collection'));
            elseif ($i == 1)
                $this->assertTrue($item->isTypeOf('entity'));
            else
                $this->fail('Unexpected');
            $i++;
        }
    }
}

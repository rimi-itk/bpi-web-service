<?php
namespace Bpi\Sdk\Tests\Unit;

use Symfony\Component\DomCrawler\Crawler;
use Bpi\Sdk\Document;

class HypermediaEngineTest extends \PHPUnit_Framework_TestCase
{
    protected function createMockClient()
    {
        $client = $this->getMock('Goutte\Client');
        $client->expects($this->at(0))
              ->method('request')
              ->with($this->equalTo('GET'), $this->equalTo('http://example.com'))
              ->will($this->returnValue(new Crawler(file_get_contents(__DIR__ . '/Fixtures/Node.bpi'))));
        
        return $client;
    }

    public function testLink()
    {
        $client = $this->createMockClient();
        
        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        $properties = $doc->link('collection')->toArray();
        $this->assertEquals('collection', $properties['rel']);
        $this->assertEquals('http://example.com/collection', $properties['href']);
        $this->assertEquals('Collection', $properties['title']);
    }
    
    public function testFollowLink()
    {
        $client = $this->createMockClient();
        
        $client->expects($this->at(1))
              ->method('request')
              ->with($this->equalTo('GET'), $this->equalTo('http://example.com/collection'))
              ->will($this->returnValue(new Crawler('<test><foo></test>')));
        
        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        $doc->followLink($doc->link('collection'));
        $this->assertEquals(1, $doc->getCrawler()->filter('foo')->count(), 'Expected foo tag in response');
    }
    
    public function testQuery()
    {
        $client = $this->createMockClient();
        
        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        $dump = $doc->query('search')->toArray();
        $this->assertEquals('search', $dump['rel']);
        $this->assertEquals('http://example.com/search', $dump['href']);
        $this->assertEquals('Search', $dump['title']);
        $this->assertEquals('id', $dump['params']['name']);
    }
    
    public function testSendQuery()
    {
        $client = $this->createMockClient();
        $client->expects($this->at(1))
              ->method('request')
              ->with($this->equalTo('GET'), $this->equalTo('http://example.com/search'), $this->equalTo(array('id' => 'foo')))
              ->will($this->returnValue(new Crawler(file_get_contents(__DIR__ . '/Fixtures/Node.bpi'))));
        
        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        $doc->sendQuery($doc->query('search'), array('id' => 'foo'));
    }
    
    /**
     * @expectedException \Bpi\Sdk\Exception\InvalidQueryParameter
     */
    public function testSendQuery_WithInvalidParameter()
    {
        $client = $this->createMockClient();
        
        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        $doc->sendQuery($doc->query('search'), array('id' => 'foo', 'zoo' => 'foo'));
    }

    public function testTemplate()
    {
        $client = $this->createMockClient();

        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        $self = $this;
        $doc->template('push')->eachField(function($field) use($self) {
            $self->assertNotEmpty((string) $field);
        });
    }

    public function testPostTemplate()
    {
        $client = $this->createMockClient();
        $post_data = array('title' => 'title', 'teaser' => 'teaser', 'body' => 'body', 'type' => 'type');
        $client->expects($this->at(1))
              ->method('request')
              ->with($this->equalTo('POST'), $this->equalTo('http://example.com/node'), $this->equalTo($post_data))
              ->will($this->returnValue(new Crawler(file_get_contents(__DIR__ . '/Fixtures/Node.bpi'))));

        $doc = new Document($client);
        $doc->request('GET', 'http://example.com');
        $doc->template('push')->eachField(function($field) {
              $field->setValue((string) $field);
        })->post($doc);
    }
}

<?php
namespace Bpi\RestMediaTypeBundle\Tests;

use Bpi\RestMediaTypeBundle\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
	public function testCursor()
	{
		$doc = new Document();
		$foo = $doc->createEntity('foo');
		$doc->appendEntity($foo);
		$this->assertEquals(spl_object_hash($foo), spl_object_hash($doc->currentEntity()));
		
		$bar = $doc->createEntity('bar');
		$doc->appendEntity($bar);
		$this->assertEquals(spl_object_hash($bar), spl_object_hash($doc->currentEntity()));
		
		$zoo = $doc->createEntity('zoo');
		$bar->addChildEntity($zoo);
		$this->assertEquals(spl_object_hash($zoo), spl_object_hash($doc->currentEntity()));
	}
	
	public function testCantSetCursorOnForeignEntity()
	{
		$doc = new Document();
		$doc->appendEntity($doc->createEntity('bar'));
		
		$doc2 = new Document();
		$foo = $doc2->createEntity('foo');
		
		$doc->setCursorOnEntity($foo);
		$this->assertNotEquals(spl_object_hash($foo), spl_object_hash($doc->currentEntity()));
	}
	
	public function testAttachDocumentToEntity()
	{
		$doc = new Document();
		$foo = $doc->createEntity('foo');
		$this->assertTrue($foo->isOwner($doc));
	}
	
	public function testChangeOwnerOnAddEntity()
	{
		$doc = new Document();		
		$doc2 = new Document();
		$bar = $doc2->createEntity('bar');
		
		$doc->appendEntity($bar);
		$this->assertTrue($bar->isOwner($doc));
	}
}
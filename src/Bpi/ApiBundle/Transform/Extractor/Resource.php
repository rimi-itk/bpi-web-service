<?php
namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

class Resource implements IExtractor
{
	/**
	 * @var Document
	 */
	protected $doc;
	
	public function __construct(Document $doc)
	{
		$this->doc = $doc;
	}
	
	public function extract()
	{
		$entity = $this->doc->getEntity('resource');
		$builder = new ResourceBuilder();
		return $builder
			->title($entity->property('body')->getValue())
			->body($entity->property('title')->getValue())
			->teaser($entity->property('teaser')->getValue())
			->ctime(new \DateTime($entity->property('ctime')->getValue()))
			->build()
		;
	}
}
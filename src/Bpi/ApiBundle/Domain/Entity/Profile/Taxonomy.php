<?php
namespace Bpi\ApiBundle\Domain\Entity\Profile;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;

class Taxonomy implements IPresentable
{
	protected $audience;
	protected $category;
//	protected $type;
//	protected $tags;

	public function __construct(Audience $audience, Category $category)
	{
		$this->audience = $audience;
		$this->category = $category;
	}
	
//	public function changeCategory(Category $category)
//	{
//		$this->category = $category;
//	}
	
	public function transform(Document $document)
	{
		$entity = $document->currentEntity();
		$entity->addProperty($document->createProperty(
			'category',
			'string',			
			$this->category->name()
		));
		$entity->addProperty($document->createProperty(
			'audience',
			'string',
			$this->audience->name()
		));
	}
}
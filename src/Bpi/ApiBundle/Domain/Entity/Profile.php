<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy;
use Bpi\ApiBundle\Domain\Entity\Profile\Relation\IRelation;

class Profile implements IPresentable
{
	protected $taxonomy;
	protected $relations;

	public function __construct(Taxonomy $taxonomy)
	{
		$this->taxonomy = $taxonomy;
		$this->relations = new \SplObjectStorage();
	}
	
	public function addRelation(IRelation $relation)
	{
		$this->attach($relation);
	}
	
	public function transform(Document $document)
	{
		$document->currentEntity()->addChildEntity(
			$document->createEntity('profile')
		);
		
		$this->taxonomy->transform($document);
	}
}
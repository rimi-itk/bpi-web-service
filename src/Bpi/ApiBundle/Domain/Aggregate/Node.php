<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;

class Node implements IPresentable
{
	protected $id;
	protected $ctime;
	protected $mtime;
	
	protected $path;
	protected $parent;
	protected $level = 0;
	protected $lock_time;
	
//	protected $comment;
	protected $resource;
	protected $profile;

	public function __construct(Resource $resource, Profile $profile)
	{
//		$this->id = $id;
		
		$this->resource = $resource;
		$this->profile = $profile;
		
		$this->markTimes();
	}
	
	protected function markTimes()
	{
		$this->mtime = $this->ctime = new \DateTime('now');
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Compare two instances by field. Need by sorting
	 * 
	 * @param \Bpi\ApiBundle\Domain\Aggregate\Node $node
	 * @param string $field can be compound like profile.taxonomy.category
	 * @param int $order 1=asc, -1=desc
	 * @return int see strcmp PHP function
	 */
	public function compare(Node $node, $field, $order = 1)
	{
		if (stristr($field, '.'))
		{
			list($local_field, $child_field) = explode(".", $field, 2);
			return $this->$local_field->compare($node->$local_field, $child_field, $order);
		}
		
		$cmp = new Comparator($this->$field, $node->$field, $order);
		return $cmp->getResult();
	}
	
	/**
	 * Calculate similarity of resources
	 * 
	 * @param Resource $resource
	 * @return boolean
	 */
	protected function isSimilarResource(Resource $resource)
	{
		return $this->resource->isSimilar($resource);
	}
	
	/**
	 * Create new revision of current node
	 * 
	 * @param Resource $resource
	 * @return Node
	 */
	public function createRevision(Resource $resource)
	{
		$builder = new \Bpi\ApiBundle\Domain\Factory\NodeBuilder;
		$builder->profile($this->profile);
		$builder->resource($resource);
		$node = $builder->build();
		
		$node->parent = $this;
		return $node;
	}

	/**
	 * @inheritDoc
	 */
	public function transform(Document $document)
	{
		$entity = $document->createEntity('node');
		
		$entity->addProperty($document->createProperty(
			'ctime',
			'date',
			$this->ctime->format('Y')
		));
		
		$entity->addProperty($document->createProperty(
			'id',
			'string',
			$this->getId()
		));
		$document->appendEntity($entity);
		
		$this->profile->transform($document);
		$document->setCursorOnEntity($entity);
		$this->resource->transform($document);
	}
}
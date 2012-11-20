<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;

class Node implements IPresentable
{
	protected $id;
	protected $ctime;
	protected $mtime;
	
//	protected $comment;
	protected $agency;
	protected $resource;
	protected $profile;

	public function __construct(Agency $agency, Resource $resource, Profile $profile)
	{
//		$this->id = $id;
		
		$this->agency = $agency;
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
	
//	public function hierarchy()
//	{
//	}
	
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
	}
}
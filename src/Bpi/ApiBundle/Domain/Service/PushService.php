<?php
namespace Bpi\ApiBundle\Domain\Service;

use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;

class PushService
{
	protected $manager;
	
	public function __construct(ObjectManager $manager)
	{
		$this->manager = $manager;
	}
	
	public function push(Author $author, Resource $resource, Profile $profile)
	{
		$builder = new NodeBuilder;
		$node = $builder
			->author($author)
			->profile($profile)
			->resource($resource)
			->build()
		;
		
		$this->manager->persist($node);
		$this->manager->flush();
		
		//TODO: raise an event
		
		return $node;
	}
	
	public function pushRevision(NodeId $node_id, Author $author, Resource $resource)
	{
		$node = $this->manager->getRepository('BpiApiBundle:Aggregate\Node')->findOneById($node_id->id());
		
		$revision = $node->createRevision($author, $resource);
		
		$this->manager->persist($revision);
		$this->manager->flush();
		
		//TODO: raise an event
		
		return $revision;
	}
}
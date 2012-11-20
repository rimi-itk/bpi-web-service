<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;

class Agency
{
	protected $id;
	
	public function __construct(AgencyId $id)
	{
		$this->id = $id;
	}
	
	public function push(Resource $resource, Profile $profile)
	{
		$builder = new NodeBuilder;
		return $builder->agency($this)
			->profile($profile)
			->resource($resource)
			->build()
		;
	}
	
	public function syndicate(NodeId $node_id)
	{
		// coll stuff here
	}
	
	protected function own($resource)
	{
		
	}
}
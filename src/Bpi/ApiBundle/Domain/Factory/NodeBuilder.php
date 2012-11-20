<?php
namespace Bpi\ApiBundle\Domain\Factory;

use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Aggregate\Agency;

class NodeBuilder
{
	protected $agency;
	protected $profile;
	protected $resource;
	
	public function agency(Agency $agency)
	{
		$this->agency = $agency;
		return $this;
	}

	public function profile(Profile $profile)
	{
		$this->profile = $profile;
		return $this;
	}
	
	public function resource(Resource $resource)
	{
		$this->resource = $resource;
		return $this;
	}
	
	public function build()
	{
		if (is_null($this->agency))
			throw new \RuntimeException('Invalid state: Agency is required');
		
		if (is_null($this->profile))
			throw new \RuntimeException('Invalid state: Profile is required');
		
		if (is_null($this->resource))
			throw new \RuntimeException('Invalid state: Resource is required');
		
		return new Node($this->agency, $this->resource, $this->profile);
	}
}
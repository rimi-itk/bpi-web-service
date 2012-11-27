<?php
namespace Bpi\ApiBundle\Domain\Command;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Entity\Resource;

class PushRevisionCommand implements ICommand
{
	protected $agency;
	protected $resource;
	protected $parent;

	public function __construct(Agency $agency, Resource $resource)
	{
		$this->agency = $agency;
		$this->resource = $resource;
	}
	
	public function setParent(Node $node)
	{
		$this->parent = $node;
	}
	
	public function execute()
	{
		if (is_null($this->parent))
			throw new \RuntimeException('Parent node is undefined');
		
		return $this->parent->createRevision($this->resource);
	}
}

<?php
namespace Bpi\ApiBundle\Domain\Command;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;

class PushCommand implements ICommand
{
	protected $agency;
	protected $profile;
	protected $resource;
	
	public function __construct(Agency $agency, Profile $profile, Resource $resource)
	{
		$this->agency = $agency;
		$this->profile = $profile;
		$this->resource = $resource;
	}
	
	public function execute()
	{
		return $this->agency->push($this->resource, $this->profile);
	}
}

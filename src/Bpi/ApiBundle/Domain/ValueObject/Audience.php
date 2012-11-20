<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

use Bpi\ApiBundle\Domain\Repository\AudienceRepository;

class Audience implements IValueObject
{
	protected $name;

	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function name()
	{
		return $this->name;
	}
	
	/**
	 * @param \Bpi\ApiBundle\Domain\ValueObject\Audience $audience
	 * @return boolean
	 */
	public function equals(IValueObject $audience)
	{
		if (get_class($this) != get_class($audience))
			return false;
		
		return $this->name() == $audience->name();
	}
	
	public function isInRepository(AudienceRepository $repository)
	{
		return $repository->findAll()->contains($this);
	}
}
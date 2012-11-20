<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

use Bpi\ApiBundle\Domain\Repository\CategoryRepository;

class Category implements IValueObject
{
	const undefined = 'undefined';
	
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
	 * @param \Bpi\ApiBundle\Domain\ValueObject\Category $category
	 * @return boolean
	 */
	public function equals(IValueObject $category)
	{
		if (get_class($this) != get_class($category))
			return false;
		
		return $this->name() == $category->name();
	}
	
	/**
	 * @param \Bpi\ApiBundle\Domain\Repository\CategoryRepository $repository
	 * @return boolean
	 */
	public function isInRepository(CategoryRepository $repository)
	{
		return $repository->findAll()->contains($this);
	}
}
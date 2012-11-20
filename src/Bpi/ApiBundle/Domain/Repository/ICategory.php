<?php
namespace Bpi\ApiBundle\Domain\Repository;

interface ICategory
{
	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function findAll();
}

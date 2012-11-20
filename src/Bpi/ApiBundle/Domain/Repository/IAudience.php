<?php
namespace Bpi\ApiBundle\Domain\Repository;

interface IAudience
{
	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function findAll();
}

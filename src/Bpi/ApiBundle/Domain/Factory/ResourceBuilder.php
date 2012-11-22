<?php
namespace Bpi\ApiBundle\Domain\Factory;

use Bpi\ApiBundle\Domain\Entity\Resource;

class ResourceBuilder
{
	protected $title, $body, $userId, $teaser, $ctime;
	
	/**
	 * 
	 * @param string $title
	 * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
	 */
	public function title($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * 
	 * @param string $body
	 * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
	 */
	public function body($body)
	{
		$this->body = $body;
		return $this;
	}
	
	/**
	 * 
	 * @param string $id
	 * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
	 */
	public function userId($id)
	{
		$this->userId = $id;
		return $this;
	}
	
	/**
	 * 
	 * @param string $teaser
	 * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
	 */
	public function teaser($teaser)
	{
		$this->teaser = $teaser;
		return $this;
	}
	
	/**
	 * 
	 * @param \DateTime $dt
	 * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
	 */
	public function ctime(\DateTime $dt)
	{
		$this->ctime = $dt;
		return $this;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	protected function isValidForBuild()
	{
		return !(is_null($this->title)
			|| is_null($this->body)
			|| is_null($this->userId)
			|| is_null($this->teaser)
			|| is_null($this->ctime)
		);
	}

	/**
	 * 
	 * @return Resource
	 * @throws \RuntimeException
	 */
	public function build()
	{
		if (!$this->isValidForBuild())
			throw new \RuntimeException('Invalid state: can not build');
		
		return new Resource($this->title, $this->body, $this->userId, $this->teaser, $this->ctime);
	}
}
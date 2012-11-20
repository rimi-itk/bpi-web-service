<?php
namespace Bpi\ApiBundle\Domain\Factory;

use Bpi\ApiBundle\Domain\Entity\Resource;

class ResourceBuilder
{
	protected $title, $body, $userId, $teaser, $ctime;
	
	public function title($title)
	{
		$this->title = $title;
		return $this;
	}

	public function body($body)
	{
		$this->body = $body;
		return $this;
	}
	
	public function userId($id)
	{
		$this->userId = $id;
		return $this;
	}
	
	public function teaser($teaser)
	{
		$this->teaser = $teaser;
		return $this;
	}
	
	public function ctime(\DateTime $dt)
	{
		$this->ctime = $dt;
		return $this;
	}
	
	protected function isValidForBuild()
	{
		return !(is_null($this->title)
			|| is_null($this->body)
			|| is_null($this->userId)
			|| is_null($this->teaser)
			|| is_null($this->ctime)
		);
	}

	public function build()
	{
		if (!$this->isValidForBuild())
			throw new \RuntimeException('Invalid state: can not build');
		
		return new Resource($this->title, $this->body, $this->userId, $this->teaser, $this->ctime);
	}
}
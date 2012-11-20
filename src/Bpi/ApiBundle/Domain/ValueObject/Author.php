<?php
namespace Bpi\ApiBundle\Domain\Aggregate\ValueObject;

class Author
{
	protected $firstname;
	protected $lastname;

	public function __construct($firstname, $lastname)
	{
		$this->firstname = $firstname;
		$this->lastname = $lastname;
	}
	
	public function credintials()
	{
		return $this->firstname . ' ' . $this->lastname;
	}
}
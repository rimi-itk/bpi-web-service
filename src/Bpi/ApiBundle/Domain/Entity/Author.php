<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Domain\ValueObject\AgencyId;

class Author
{
	protected $agency_id;
	protected $client_id;
	protected $firstname;
	protected $lastname;

	public function __construct(AgencyId $agency_id, $client_id, $lastname, $firstname = null)
	{
		$this->agency_id = $agency_id;
		$this->client_id = $client_id;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
	}
	
	public function getAgencyId()
	{
		return $this->agency_id;
	}
	
	public function credintials()
	{
		return ($this->firstname ? $this->firstname.' ' : '') . $this->lastname;
	}
}
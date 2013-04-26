<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;

class Author
{
    /**
     *
     * @var Bpi\ApiBundle\Domain\ValueObject\AgencyId
     */
    protected $agency_id;

    /**
     *
     * @var string
     */
    protected $client_id;

    /**
     *
     * @var string
     */
    protected $firstname;

    /**
     *
     * @var string|null
     */
    protected $lastname;

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\AgencyId $agency_id
     * @param string $client_id Author ID in client local system
     * @param string $lastname
     * @param string|null $firstname
     */
    public function __construct(AgencyId $agency_id, $client_id, $lastname, $firstname = null)
    {
        $this->agency_id = $agency_id;
        $this->client_id = $client_id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\ValueObject\AgencyId $agency_id
     */
    public function getAgencyId()
    {
        return $this->agency_id;
    }

    /**
     *
     * @return string
     */
    public function getFullName()
    {
        return ($this->firstname ? $this->firstname.' ' : '') . $this->lastname;
    }

    /**
     * Set autorship
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Copyleft $copyleft
     */
    public function setAuthorship(Copyleft $copyleft)
    {
        $copyleft->addCopyrigher($this->getFullName(), false);
    }
}

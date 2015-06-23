<?php

namespace Bpi\ApiBundle\Domain\Entity;



/**
 * Bpi\ApiBundle\Domain\Entity\User
 */
class User
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var Bpi\ApiBundle\Aggregate\Agency
     */
    protected $userAgency;


    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set userAgency
     *
     * @param Bpi\ApiBundle\Aggregate\Agency $userAgency
     * @return self
     */
    public function setUserAgency(\Bpi\ApiBundle\Aggregate\Agency $userAgency)
    {
        $this->userAgency = $userAgency;
        return $this;
    }

    /**
     * Get userAgency
     *
     * @return Bpi\ApiBundle\Aggregate\Agency $userAgency
     */
    public function getUserAgency()
    {
        return $this->userAgency;
    }
}
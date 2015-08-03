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
     * @var string $externalId
     */
    protected $externalId;

    /**
     * @var string $internalUserName
     */
    protected $internalUserName;

    /**
     * @var string $email
     */
    protected $email;

    /**
     * @var string $userFirstName
     */
    protected $userFirstName;

    /**
     * @var string $userLastName
     */
    protected $userLastName;

    /**
     * @var \Bpi\ApiBundle\Domain\Aggregate\Agency
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
     * Set externalId
     *
     * @param string $externalId
     * @return self
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * Get externalId
     *
     * @return string $externalId
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set internalUserName
     *
     * @param bool $byEmail
     * @return self
     */
    public function setInternalUserName($byEmail = false)
    {
        $nameFromEmail = explode('@', $this->email);
        $internalUserName = $nameFromEmail[0];

        if(!empty($this->userFirstName) || $this->userLastName && !$byEmail) {
            $internalUserName = $this->userFirstName . $this->userLastName;
        }

        $internalUserName = preg_replace('/[^a-zA-Z0-9]/', '', $internalUserName);

        $this->internalUserName = $internalUserName;
        return $this;
    }

    /**
     * Get internalUserName
     *
     * @return string $internalUserName
     */
    public function getInternalUserName()
    {
        return $this->internalUserName;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set userFirstName
     *
     * @param string $userFirstName
     * @return self
     */
    public function setUserFirstName($userFirstName)
    {
        $this->userFirstName = $userFirstName;
        return $this;
    }

    /**
     * Get userFirstName
     *
     * @return string $userFirstName
     */
    public function getUserFirstName()
    {
        return $this->userFirstName;
    }

    /**
     * Set userLastName
     *
     * @param string $userLastName
     * @return self
     */
    public function setUserLastName($userLastName)
    {
        $this->userLastName = $userLastName;
        return $this;
    }

    /**
     * Get userLastName
     *
     * @return string $userLastName
     */
    public function getUserLastName()
    {
        return $this->userLastName;
    }

    /**
     * Set userAgency
     *
     * @param \Bpi\ApiBundle\Domain\Aggregate\Agency $userAgency
     * @return self
     */
    public function setUserAgency(\Bpi\ApiBundle\Domain\Aggregate\Agency $userAgency)
    {
        $this->userAgency = $userAgency;
        return $this;
    }

    /**
     * Get userAgency
     *
     * @return \Bpi\ApiBundle\Domain\Aggregate\Agency $userAgency
     */
    public function getUserAgency()
    {
        return $this->userAgency;
    }
}

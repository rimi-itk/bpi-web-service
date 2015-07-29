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
     * @param string $internalUserName
     * @return self
     */
    public function setInternalUserName($internalUserName)
    {
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
}
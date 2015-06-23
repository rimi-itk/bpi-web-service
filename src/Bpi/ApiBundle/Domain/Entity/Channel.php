<?php

namespace Bpi\ApiBundle\Domain\Entity;



/**
 * Bpi\ApiBundle\Domain\Entity\Channel
 */
class Channel
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
     * @var string $description
     */
    protected $description;

    /**
     * @var Bpi\ApiBundle\Domain\Entity\User
     */
    protected $owner;

    /**
     * @var Bpi\ApiBundle\Domain\Entity\User
     */
    protected $users = array();

    /**
     * @var Bpi\ApiBundle\Domain\Aggregate\Node
     */
    protected $nodes = array();

    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->nodes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set owner
     *
     * @param Bpi\ApiBundle\Domain\Entity\User $owner
     * @return self
     */
    public function setOwner(\Bpi\ApiBundle\Domain\Entity\User $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get owner
     *
     * @return Bpi\ApiBundle\Domain\Entity\User $owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add user
     *
     * @param Bpi\ApiBundle\Domain\Entity\User $user
     */
    public function addUser(\Bpi\ApiBundle\Domain\Entity\User $user)
    {
        $this->users[] = $user;
    }

    /**
     * Remove user
     *
     * @param Bpi\ApiBundle\Domain\Entity\User $user
     */
    public function removeUser(\Bpi\ApiBundle\Domain\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection $users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add node
     *
     * @param Bpi\ApiBundle\Domain\Aggregate\Node $node
     */
    public function addNode(\Bpi\ApiBundle\Domain\Aggregate\Node $node)
    {
        $this->nodes[] = $node;
    }

    /**
     * Remove node
     *
     * @param Bpi\ApiBundle\Domain\Aggregate\Node $node
     */
    public function removeNode(\Bpi\ApiBundle\Domain\Aggregate\Node $node)
    {
        $this->nodes->removeElement($node);
    }

    /**
     * Get nodes
     *
     * @return Doctrine\Common\Collections\Collection $nodes
     */
    public function getNodes()
    {
        return $this->nodes;
    }
}
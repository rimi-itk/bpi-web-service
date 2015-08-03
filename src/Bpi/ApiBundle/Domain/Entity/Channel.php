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
     * @var string $channelName
     */
    protected $channelName;

    /**
     * @var string $channelDescription
     */
    protected $channelDescription;

    /**
     * @var boolean $channelDeleted
     */
    protected $channelDeleted = false;

    /**
     * @var Bpi\ApiBundle\Domain\Entity\User
     */
    protected $channelAdmin;

    /**
     * @var object
     */
    protected $channelEditors = array();

    /**
     * @var object
     */
    protected $channelNodes = array();

    public function __construct()
    {
        $this->channelEditors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->channelNodes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set channelName
     *
     * @param string $channelName
     * @return self
     */
    public function setChannelName($channelName)
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * Get channelName
     *
     * @return string $channelName
     */
    public function getChannelName()
    {
        return $this->channelName;
    }

    /**
     * Set channelDescription
     *
     * @param string $channelDescription
     * @return self
     */
    public function setChannelDescription($channelDescription)
    {
        $this->channelDescription = $channelDescription;
        return $this;
    }

    /**
     * Get channelDescription
     *
     * @return string $channelDescription
     */
    public function getChannelDescription()
    {
        return $this->channelDescription;
    }

    /**
     * Set channelDeleted
     *
     * @param boolean $channelDeleted
     * @return self
     */
    public function setChannelDeleted($channelDeleted)
    {
        $this->channelDeleted = $channelDeleted;
        return $this;
    }

    /**
     * Get channelDeleted
     *
     * @return boolean $channelDeleted
     */
    public function getChannelDeleted()
    {
        return $this->channelDeleted;
    }

    /**
     * Set channelAdmin
     *
     * @param Bpi\ApiBundle\Domain\Entity\User $channelAdmin
     * @return self
     */
    public function setChannelAdmin(\Bpi\ApiBundle\Domain\Entity\User $channelAdmin)
    {
        $this->channelAdmin = $channelAdmin;
        return $this;
    }

    /**
     * Get channelAdmin
     *
     * @return Bpi\ApiBundle\Domain\Entity\User $channelAdmin
     */
    public function getChannelAdmin()
    {
        return $this->channelAdmin;
    }

    /**
     * Add channelEditor
     *
     * @param $channelEditor
     */
    public function addChannelEditor($channelEditor)
    {
        $this->channelEditors[] = $channelEditor;
    }

    /**
     * Remove channelEditor
     *
     * @param $channelEditor
     */
    public function removeChannelEditor($channelEditor)
    {
        $this->channelEditors->removeElement($channelEditor);
    }

    /**
     * Get channelEditors
     *
     * @return Doctrine\Common\Collections\Collection $channelEditors
     */
    public function getChannelEditors()
    {
        return $this->channelEditors;
    }

    /**
     * Add channelNode
     *
     * @param $channelNode
     */
    public function addChannelNode($channelNode)
    {
        $this->channelNodes[] = $channelNode;
    }

    /**
     * Remove channelNode
     *
     * @param $channelNode
     */
    public function removeChannelNode($channelNode)
    {
        $this->channelNodes->removeElement($channelNode);
    }

    /**
     * Get channelNodes
     *
     * @return Doctrine\Common\Collections\Collection $channelNodes
     */
    public function getChannelNodes()
    {
        return $this->channelNodes;
    }
}
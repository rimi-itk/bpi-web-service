<?php

namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Transform\IPresentable;
use Doctrine\Common\Collections\ArrayCollection;
use Bpi\ApiBundle\Domain\Entity\User;
use Bpi\RestMediaTypeBundle\Document;

/**
 * Bpi\ApiBundle\Domain\Entity\Channel
 */
class Channel implements IPresentable
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
     * @var \Bpi\ApiBundle\Domain\Entity\User
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
        $this->channelEditors = new ArrayCollection();
        $this->channelNodes = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string $id of entity
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
     * @param \Bpi\ApiBundle\Domain\Entity\User $channelAdmin
     * @return self
     */
    public function setChannelAdmin(User $channelAdmin)
    {
        $this->channelAdmin = $channelAdmin;
        return $this;
    }

    /**
     * Get channelAdmin
     *
     * @return \Bpi\ApiBundle\Domain\Entity\User $channelAdmin
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
     * @return \Doctrine\Common\Collections\Collection $channelEditors
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
     * @return \Doctrine\Common\Collections\Collection $channelNodes
     */
    public function getChannelNodes()
    {
        return $this->channelNodes;
    }

    public function transform(Document $document)
    {
        $entity = $document->createEntity('entity', 'channel');

        $entity->addProperty(
            $document->createProperty(
                'id',
                'string',
                $this->getId()
            )
        );

        $entity->addProperty(
            $document->createProperty(
                'channelName',
                'string',
                $this->getChannelName()
            )
        );

        $entity->addProperty(
            $document->createProperty(
                'channelDescription',
                'string',
                $this->getChannelDescription()
            )
        );

        if (!empty($this->getChannelAdmin())) {
            $entity->addProperty(
                $document->createProperty(
                    'channelAdmin',
                    'string',
                    $this->getChannelAdmin()->getInternalUserName()
                )
            );
        }

        foreach ($this->channelEditors as $editor) {
            $entity->addProperty(
                $document->createProperty(
                    'editor',
                    'string',
                    $editor->getInternalUserName()
                )
            );
        }

        $document->appendEntity($entity);

        $document->setCursorOnEntity($entity);
        foreach ($this->channelNodes as $node) {
            $node->transform($node);
        }
    }
}

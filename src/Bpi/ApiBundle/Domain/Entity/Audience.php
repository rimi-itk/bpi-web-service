<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Transform\IPresentable;

class Audience implements IPresentable
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $audience;

    /**
     * @param string $audience
     */
    public function __construct($audience = null)
    {
        $this->setAudience($audience);
    }

    /**
     * Set audience.
     *
     * @param string $audience
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;
    }

    /**
     * Get audience name.
     *
     * @return string
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * Get audience ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Bpi\RestMediaTypeBundle\Document $document
     */
    public function transform(\Bpi\RestMediaTypeBundle\Document $document)
    {
        $entity = $document->createEntity('audience');
        $document->appendEntity($entity);

        $entity->addProperty($document->createProperty('group', 'string', 'audience'));
        $entity->addProperty($document->createProperty('name', 'string', $this->getAudience()));
    }
}

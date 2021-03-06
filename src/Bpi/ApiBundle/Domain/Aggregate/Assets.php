<?php

namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Domain\ValueObject\ValueObjectList;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\RestMediaTypeBundle\XmlResponse;

/**
 * Bpi\ApiBundle\Domain\Aggregate\Assets
 */
class Assets
{
    /**
     * @var object
     */
    protected $collection = [];

    public function __construct(array $collection = [])
    {
        $this->collection = new ValueObjectList($collection);
    }

    /**
     * Add collection
     *
     * @param $collection
     */
    public function addCollection($collection)
    {
        $this->collection[] = $collection;
    }

    /**
     * Remove collection
     *
     * @param $collection
     */
    public function removeCollection($collection)
    {
        $this->collection->removeElement($collection);
    }

    /**
     * Get collection
     *
     * @return Doctrine\Common\Collections\Collection $collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    public function addElem($elem)
    {
        $this->collection->add($elem);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(XmlResponse $document)
    {
        try {
            $entity = $document->currentEntity();
        } catch (\RuntimeException $e) {
            $entity = $document->createEntity('entity', 'assets');
            $document->appendEntity($entity);
        }

        foreach ($this->collection as $asset) {
            $entity->addAsset($asset);
        }
    }
}

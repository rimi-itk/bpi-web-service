<?php

namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;
use Bpi\RestMediaTypeBundle\XmlResponse;

/**
 * Statistics entity
 */
class Statistics implements IPresentable
{
    protected $stats;

    public function __construct(array $stats = [])
    {
        $this->stats = $stats;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(XmlResponse $document)
    {
        try {
            $entity = $document->currentEntity();
        } catch (\RuntimeException $e) {
            $entity = $document->createEntity('entity', 'statistics');
            $document->appendEntity($entity);
        }

        $fields = ['push', 'syndicate'];

        foreach ($fields as $field) {
            $value = 0;
            if (isset($this->stats[$field])) {
                $value = $this->stats[$field];
            }
            $entity->addProperty($document->createProperty($field, 'string', $value));
        }
    }

    public function getStats()
    {
        return $this->stats;
    }
}

<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;

/**
 * Statistics entity
 */
class Statistics implements IPresentable
{
  private $stats;

    public function __construct(array $stats = array())
    {
      $this->stats = $stats;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Document $document)
    {
        try {
            $entity= $document->currentEntity();
        } catch (\RuntimeException $e) {
            $entity = $document->createEntity('entity', 'statistics');
            $document->appendEntity($entity);
        }

        foreach ($this->stats as $key => $value) {
          $entity->addProperty($document->createProperty($key, 'string', $value));
        }
    }
}

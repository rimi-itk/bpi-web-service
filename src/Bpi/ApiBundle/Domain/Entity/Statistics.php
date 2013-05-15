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

        $fields = array('push', 'syndicate');

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

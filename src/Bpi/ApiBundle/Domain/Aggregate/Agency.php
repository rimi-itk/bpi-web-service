<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;

class Agency implements IPresentable
{
    protected $id;

    protected $publickey;

    /**
     * {@inheritdoc}
     */
    public function transform(Document $document)
    {
        $document->appendEntity($entity = $document->createEntity('agency'));
        $entity->addProperty($document->createProperty('id', 'string', $this->id));
    }
}

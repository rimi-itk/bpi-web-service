<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;

class Agency implements IPresentable
{
    protected $id;

    protected $name;

    protected $moderator;

    protected $public_key;

    protected $secret;

    public function __construct($name, $moderator, $public_key, $secret)
    {
        $this->name = $name;
        $this->moderator = $moderator;
        $this->publickey = $public_key;
        $this->secret = $secret;
    }

        /**
     * {@inheritdoc}
     */
    public function transform(Document $document)
    {
        $document->appendEntity($entity = $document->createEntity('agency'));
        $entity->addProperty($document->createProperty('name', 'string', $this->name));
        $entity->addProperty($document->createProperty('moderator', 'string', $this->moderator));
        $entity->addProperty($document->createProperty('public_key', 'string', $this->public_key));
    }
}

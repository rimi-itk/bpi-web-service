<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;

class Agency implements IPresentable
{
    protected $id;

    protected $public_id;

    protected $name;

    protected $moderator;

    protected $public_key;

    protected $secret;

    public function __construct($public_id, $name, $moderator, $public_key, $secret)
    {
        $this->public_id = $public_id;
        $this->name = $name;
        $this->moderator = $moderator;
        $this->public_key = $public_key;
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Document $document)
    {
        $document->appendEntity($entity = $document->createEntity('agency'));
        $entity->addProperty($document->createProperty('public_id', 'string', $this->public_id));
        $entity->addProperty($document->createProperty('name', 'string', $this->name));
        $entity->addProperty($document->createProperty('moderator', 'string', $this->moderator));
        $entity->addProperty($document->createProperty('public_key', 'string', $this->public_key));
    }

    /**
     * Set autorship
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Copyleft $copyleft
     */
    public function setAuthorship(Copyleft $copyleft)
    {
        $copyleft->addCopyrigher($this->name);
    }

    /**
     * Get Agency ID
     *
     * @return \Bpi\ApiBundle\Domain\ValueObject\AgencyId
     */
    public function getAgencyId()
    {
        return new AgencyId($this->public_id);
    }

    /**
     * Check auth token.
     *
     * @return string
     */
    public function checkToken($token)
    {
        $localToken = crypt($this->public_id . $this->public_key . $this->secret, $token);
        return $token === $localToken;
    }

    public function __toString()
    {
        return (string) $this->public_id;
    }
}

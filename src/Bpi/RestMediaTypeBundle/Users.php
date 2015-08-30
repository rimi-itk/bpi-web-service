<?php
namespace Bpi\RestMediaTypeBundle;

use Bpi\RestMediaTypeBundle\Element\Entity;
use Bpi\RestMediaTypeBundle\XmlResponse;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("users")
 */
class Users extends XmlResponse
{
    /**
     * @Serializer\XmlList(inline=true, entry="user")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\User>")
     */
    public $users = array();
    public function addUser(\Bpi\ApiBundle\Domain\Entity\User $entity)
    {
        $entity = new \Bpi\RestMediaTypeBundle\Element\User($entity);
        $this->users[] = $entity;
    }

}


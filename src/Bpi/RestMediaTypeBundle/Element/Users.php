<?php
namespace Bpi\RestMediaTypeBundle\Element;

use Bpi\RestMediaTypeBundle\Element\Entity;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("users")
 */
class Users extends Entity
{
    /**
     * @Serializer\XmlList(inline=true, entry="user")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\User>")
     */
    public $users = array();

    public function __construct() {}

    public function addUser(\Bpi\ApiBundle\Domain\Entity\User $entity)
    {
        $entity = new \Bpi\RestMediaTypeBundle\Element\User($entity);
        $this->users[] = $entity;
    }

}
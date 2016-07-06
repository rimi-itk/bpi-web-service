<?php
namespace Bpi\RestMediaTypeBundle;

use Bpi\ApiBundle\Domain\Entity\User;
use JMS\Serializer\Annotation as Serializer;

/**
 * Users.
 *
 * @Serializer\XmlRoot("users")
 */
class Users extends Collection
{
    /**
     * @Serializer\XmlList(inline=true, entry="user")
     */
    public $items = array();

    /**
     * Add a User to the collection
     *
     * @param User $user
     *
     * @return Collection
     */
    public function addUser(User $user)
    {
        $item = new \Bpi\RestMediaTypeBundle\Element\User($user);

        return $this->addItem($item);
    }
}

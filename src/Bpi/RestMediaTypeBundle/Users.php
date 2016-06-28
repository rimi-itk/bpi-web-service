<?php
namespace Bpi\RestMediaTypeBundle;

use Bpi\ApiBundle\Domain\Entity\Channel;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("users")
 */
class Users extends Collection
{
    /**
     * @Serializer\XmlList(inline=true, entry="user")
     */
    public $items = array();

    public function addUser(\Bpi\ApiBundle\Domain\Entity\User $entity)
    {
        $item = new \Bpi\RestMediaTypeBundle\Element\User($entity);
        return $this->addItem($item);
    }

}

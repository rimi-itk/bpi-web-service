<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("user")
 */
class User extends Item
{
    /**
     * @Serializer\Type("string")
     */
    protected $id;

    /**
     * @Serializer\Type("string")
     */
    protected $internalUserName;

    /**
     * @Serializer\Type("string")
     */
    protected $email;

    /**
     * @Serializer\Type("string")
     */
    protected $userFirstName;

    /**
     * @Serializer\Type("string")
     */
    protected $userLastName;

    /**
     * @Serializer\Type("Bpi\RestMediaTypeBundle\Element\Agency")
     */
    protected $agency;

    /**
     * @Serializer\Type("ArrayCollection<Bpi\ApiBundle\Domain\ValueObject\Subscription>")
     */
    protected $subscriptions;

    /**
     * Create a new User.
     *
     * @param \Bpi\ApiBundle\Domain\Entity\User $user
     */
    public function __construct(\Bpi\ApiBundle\Domain\Entity\User $user)
    {
        $this->id = $user->getId();
        $this->internalUserName = $user->getInternalUserName();
        $this->email = $user->getEmail();
        $this->userFirstName = $user->getUserFirstName();
        $this->userLastName = $user->getUserLastName();
        $this->agency = new Agency($user->getUserAgency());
        $this->subscriptions = $user->getSubscriptions();
    }
}

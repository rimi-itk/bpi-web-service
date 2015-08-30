<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("user")
 */
class User
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
     * @Serializer\Type("string")
     */
    protected $agencyId;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->id = $data->getId();
        $this->internalUserName = $data->getInternalUserName();
        $this->email = $data->getEmail();
        $this->userFirstName = $data->getUserFirstName();
        $this->userLastName = $data->getUserLastName();
        $this->agencyId = $data->getUserAgency()->getPublicId();
    }
}

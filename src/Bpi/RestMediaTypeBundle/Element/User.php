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
     * @Serializer\XmlAttribute
     */
    protected $id;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $internalUserName;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $email;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $userFirstName;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $userLastName;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
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

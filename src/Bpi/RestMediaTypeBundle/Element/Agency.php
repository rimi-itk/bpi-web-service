<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("agency")
 */
class Agency
{
    /**
     * @Serializer\Type("string")
     */
    protected $id;

    /**
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->id = $data->getPublicId();
        $this->name = $data->getName();
    }
}

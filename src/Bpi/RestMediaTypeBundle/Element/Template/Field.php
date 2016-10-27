<?php
namespace Bpi\RestMediaTypeBundle\Element\Template;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("field")
 */
class Field
{
    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $name;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $type;

    /**
     *
     * @param string $name
     */
    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
}

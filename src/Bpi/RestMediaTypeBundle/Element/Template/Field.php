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
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}

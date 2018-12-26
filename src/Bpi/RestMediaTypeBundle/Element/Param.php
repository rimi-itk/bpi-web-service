<?php

namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("query")
 */
class Param
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
    protected $type = 'single';

    /**
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Mark this parameter as multiple value
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Param same instance
     */
    public function setMultiple()
    {
        $this->type = 'multiple';

        return $this;
    }
}

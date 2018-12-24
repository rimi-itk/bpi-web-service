<?php

namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Tag
 *
 * @package Bpi\RestMediaTypeBundle\Element
 * @Serializer\XmlRoot("tag")
 */
class Tag
{
    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $tagName;

    public function __construct($tagName)
    {
        $this->tagName = $tagName;
    }
}

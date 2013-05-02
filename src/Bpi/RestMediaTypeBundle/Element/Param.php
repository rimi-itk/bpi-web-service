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
     * 
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}

<?php
namespace Bpi\RestMediaTypeBundle\Property;

use JMS\Serializer\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Property;

/**
 * @Serializer\XmlRoot("property")
 */
class Json extends Property
{
    /**
     * @Serializer\XmlValue
     * @Serializer\Type("string")
     */
    protected $value;
}

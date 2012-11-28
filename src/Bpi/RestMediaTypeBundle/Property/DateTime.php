<?php
namespace Bpi\RestMediaTypeBundle\Property;

use JMS\SerializerBundle\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Property;

/**
 * @Serializer\XmlRoot("property")
 */
class DateTime extends Property
{
    /**
     * @Serializer\XmlValue
     * @Serializer\Type("DateTime")
     */
    protected $value;
}

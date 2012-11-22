<?php
namespace Bpi\RestMediaTypeBundle\Property;

use JMS\SerializerBundle\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Property;

/**
 * @Serializer\XmlRoot("property")
 */
class Number extends Property
{
	/**
	 * @Serializer\XmlValue
	 * @Serializer\Type("double")
	 */
	protected $value;
}
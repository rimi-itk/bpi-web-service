<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\SerializerBundle\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Property\TypeEnum;

/**
 * @Serializer\XmlRoot("property")
 */
class Property
{
	const type_entity = 'entity';
	
	/**
	 * @Serializer\XmlAttribute
	 */
	private $type;
	
	/**
	 * @Serializer\XmlAttribute
	 */
	protected $name;
	
	/**
	 * @Serializer\XmlValue
	 */
	protected $value;
	
	/**
	 * @Serializer\XmlAttribute
	 */
	protected $title;
	
	public function __construct($type, $name, $value, $title = '')
	{
		$this->type = $type;
		$this->name = $name;
		$this->value = $value;
		$this->title = $title;
	}
	
	protected function setType(TypeEnum $enum)
	{
		$this->type = $enum->toString();
	}
}
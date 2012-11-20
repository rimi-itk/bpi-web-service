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
	 * @Serializer\Type("string")
	 */
	private $type;
	
	/**
	 * @Serializer\XmlAttribute
	 * @Serializer\Type("string")
	 */
	protected $name;
	
	/**
	 * @Serializer\XmlValue
	 * @Serializer\Type("string")
	 */
	protected $value;
	
	/**
	 * @Serializer\XmlAttribute
	 * @Serializer\Type("string")
	 */
	protected $title;
	
	/**
	 * 
	 * @param string $type
	 * @param string $name
	 * @param mixed $value
	 * @param string $title
	 */
	public function __construct($type, $name, $value, $title = '')
	{
		$this->type = $type;
		$this->name = $name;
		$this->value = $value;
		$this->title = $title;
		$this->normalizeValue();
	}
	
	/**
	 * @Serializer\PostDeserialize
	 */
	protected function normalizeValue()
	{
		if (function_exists('is_'.$this->type))
			@settype($this->value, $this->type);
	}
	
	protected function setType(TypeEnum $enum)
	{
		$this->type = $enum->toString();
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function typeOf($type)
	{
		//TODO: process the value
		return $this->type == $type;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
}
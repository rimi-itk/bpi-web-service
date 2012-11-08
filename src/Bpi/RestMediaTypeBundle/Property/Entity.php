<?php
namespace Bpi\RestMediaTypeBundle\Property;

use JMS\SerializerBundle\Annotation as Serializer;

use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\RestMediaTypeBundle\Property\TypeEnum;
use Bpi\RestMediaTypeBundle\DataType\String;
use Bpi\RestMediaTypeBundle\DataType\Entity as EntityType;

/**
 * @Serializer\XmlRoot("property")
 */
class Entity extends Property
{
	/**
	 * @Serializer\XmlAttribute
	 */
	protected $entityname;
	
	/**
	 * @Serializer\XmlList(inline=true, entry="entity")
	 */
	protected $value;
	
	public function __construct(String $name, EntityType $value, String $title)
	{
		$this->setType(new TypeEnum('entity'));
		$this->name = $name->value();
		$this->value = $value->value();
		$this->title = $title->value();
		$this->entityname = $this->value[0]->getName();
	}
}
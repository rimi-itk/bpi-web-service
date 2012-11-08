<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\SerializerBundle\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\RestMediaTypeBundle\Element\Link;
use Bpi\RestMediaTypeBundle\Element\Scope\HasLinks;

/**
 * @Serializer\XmlRoot("entity")
 */
class Entity implements HasLinks
{
	/**
	 * @Serializer\XmlAttribute
	 */
	protected $name;
	
	/**
	 * @Serializer\XmlList(entry="link")
	 * Serializer\Type("array<\Bpi\RestMediaTypeBundle\Element\Link>")
	 */
	protected $links;
	
	/**
	 * @Serializer\XmlList(entry="property")
	 * Serializer\Type("array<\Bpi\RestMediaTypeBundle\Element\Property>")
	 */
	protected $properties;
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function addProperty(Property $property)
	{
		$this->properties[] = $property;
	}
	
	public function addLink(Link $link)
	{
		$this->links[] = $link;
	}
}
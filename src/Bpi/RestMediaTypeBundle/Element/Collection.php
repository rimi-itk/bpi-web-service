<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\SerializerBundle\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Entity;
use Bpi\RestMediaTypeBundle\Element\Link;
use Bpi\RestMediaTypeBundle\Element\Scope\HasLinks;

/**
 * @Serializer\XmlRoot("collection")
 */
class Collection implements HasLinks
{
	/**
	 * @Serializer\XmlAttribute
	 */
	protected $name;
	
	/**
	 * @Serializer\XmlList(entry="entity")
	 */
	protected $items;
	
	/**
	 * @Serializer\XmlList(entry="link")
	 * Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Link>")
	 */
	protected $links;
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function add(Entity $entity)
	{
		$this->items[] = $entity;
	}
	
	public function addLink(Link $link)
	{
		$this->links[] = $link;
	}
}
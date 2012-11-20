<?php
namespace Bpi\RestMediaTypeBundle;

use JMS\SerializerBundle\Annotation as Serializer;

use Bpi\RestMediaTypeBundle\Element\Collection;
use Bpi\RestMediaTypeBundle\Element\Entity;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\RestMediaTypeBundle\Element\Link;

/**
 * @Serializer\XmlRoot("bpi")
 */
class Document
{
	/**
	 * @Serializer\Exclude
	 * @var Bpi\RestMediaTypeBundle\Element\Entity current entity
	 */
	protected $current_entity;
		
	/**
	 * @Serializer\XmlAttribute
	 * @Serializer\Type("string")
	 */
	protected $version;
	
	/**
	 * @Serializer\XmlList(inline=true, entry="entity")
	 * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Entity>")
	 */
	protected $entities;
	
	/**
	 * @Serializer\Type("Bpi\RestMediaTypeBundle\Element\Collection")
	 */
	protected $collection;
	
	public function __construct()
	{
		$this->version = '0.1';
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getEntities()
	{
		return $this->entities;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return Entity
	 * @throws \LogicException
	 */
	public function getEntity($name)
	{
		foreach($this->entities as $entity)
			if ($entity->getName() == $name)
				return $entity;
			
		throw new \LogicException('No such entity name ['.$name.']');
	}
	
	/**
	 * 
	 * @param \Bpi\RestMediaTypeBundle\Element\Collection $collection
	 */
	public function setCollection(Collection $collection)
	{
		$this->collection = $collection;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return \Bpi\RestMediaTypeBundle\Element\Entity
	 */
	public function createEntity($name)
	{
		$entity = new Entity($name);
		$entity->attach($this);
		return $entity;
	}
	
	/**
	 * Append entity to the end of document
	 * 
	 * @param \Bpi\RestMediaTypeBundle\Element\Entity $entity
	 */
	public function appendEntity(Entity $entity)
	{
		$this->entities[] = $entity;
		$this->setCursorOnEntity($entity);
		$entity->attach($this);
	}
	
	/**
	 * 
	 * @param string $name
	 * @param string $type
	 * @param string $value
	 * @param string $title
	 * @return \Bpi\RestMediaTypeBundle\Element\Property
	 */
	public function createProperty($name, $type, $value, $title = '')
	{
		return new Property($type, $name, $value, $title);
	}
	
	/**
	 * Create a link
	 * 
	 * @param string $rel
	 * @param string $href
	 * @param string $title
	 * @return \Bpi\RestMediaTypeBundle\Element\Link
	 */
	public function createLink($rel, $href, $title = '')
	{
		return new Link($rel, $href, $title);
	}
	
	/**
	 * Get last used entity
	 * 
	 * @return \Bpi\RestMediaTypeBundle\Element\Entity
	 * @throw \RuntimeException
	 */
	public function currentEntity()
	{
		if (!is_object($this->current_entity))
			throw new \RuntimeException('There is no current entities yet');
		
		return $this->current_entity;
	}
	
	/**
	 * Set internal pointer to entity
	 * 
	 * @see currentEntity()
	 * @param \Bpi\RestMediaTypeBundle\Element\Entity $entity
	 */
	public function setCursorOnEntity(Entity $entity)
	{
		if ($entity->isOwner($this))
			$this->current_entity = $entity;
	}
}
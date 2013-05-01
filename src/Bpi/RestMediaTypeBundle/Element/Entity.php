<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\RestMediaTypeBundle\Element\Link;
use Bpi\RestMediaTypeBundle\Element\Scope\HasLinks;
use Bpi\RestMediaTypeBundle\Document;

/**
 * @Serializer\XmlRoot("item")
 */
class Entity implements HasLinks
{
    /**
     * Item without name
     */
    const NONAME = 'noname';

    /**
     * @Serializer\Exclude
     */
    protected $document;

    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $type;

    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * @Serializer\XmlList(entry="link")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Link>")
     */
    protected $links;

    /**
     * @Serializer\XmlList(entry="property")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Property>")
     */
    protected $properties;

    /**
     * @Serializer\XmlList(inline=true, entry="entity")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Entity>")
     */
    protected $entities;

    /**
     * @Serializer\Type("Bpi\RestMediaTypeBundle\Element\Hypermedia")
     */
    protected $hypermedia;
    
    /**
     *
     * @param string $type
     */
    public function __construct($type, $name = null)
    {
        $this->type = $type;
        $this->name = $name ? $name : self::NONAME;
    }

    /**
     * Entity name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Property $property
     */
    public function addProperty(Property $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     *
     * @param array $properties
     */
    public function addProperties(array $properties)
    {
        foreach ($properties as $property)
            $this->addProperty($property);
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Link $link
     * @return \Bpi\RestMediaTypeBundle\Element\Entity
     */
    public function addLink(Link $link)
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     *
     * @param string $name
     * @return Property
     */
    public function property($name)
    {
        return $this->hasProperty($name) ? $this->properties[$name] : null;
    }

    /**
     * @Serializer\PostDeserialize
     */
    protected function rebuildKeys()
    {
        // after deserialization
        foreach ($this->properties as $key => $property) {
            unset($this->properties[$key]);
            $this->properties[$property->getName()] = $property;
        }

        foreach ($this->entities as $key => $entity) {
            unset($this->entities[$key]);
            $this->entities[$entity->getName()] = $entity;
        }
    }

    public function hasProperty($name, $type = null)
    {
        if (!isset($this->properties[$name]))
            return false;

        if  (!is_null($type))
            if (!$this->properties[$name]->typeOf($type))
                return false;

        return true;
    }

    /**
     * Attach this entity to document
     *
     * @param \Bpi\RestMediaTypeBundle\Document $document
     */
    public function attach(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Check if givendocument is owner of current entity
     *
     * @param \Bpi\RestMediaTypeBundle\Document $document
     * @return bool
     */
    public function isOwner(Document $document)
    {
        return spl_object_hash($this->document) == spl_object_hash($document);
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Entity $entity
     */
    public function addChildEntity(Entity $entity)
    {
        $this->document->setCursorOnEntity($entity);
        $this->entities[$entity->getName()] = $entity;
    }

    /**
     *
     * @param string $name of entity
     * @return Entity
     */
    public function getChildEntity($name)
    {
        return $this->entities[$name];
    }

    /**
     * Get properties matched by name
     *
     * @param string $regexp name regular expression
     * @return array
     */
    public function matchProperties($regexp)
    {
        $props = array();
        foreach ($this->properties as $property) {
            if (preg_match($regexp, $property->getName(), $matches))
                $props[$matches[1]] = $property;
        }
        return $props;
    }

    /**
     *
     * @param \Closure $callback with value, index arguments
     */
    public function walk(\Closure $callback)
    {
        array_walk($this->properties, $callback);
    }

    /**
     * Get properties matched by type
     *
     * @param string $type
     * @return array
     */
    public function matchPropertiesByType($type)
    {
        $props = array();
        foreach ($this->properties as $property) {
            if ($property->typeOf($type))
                $props[$property->getName()] = $property;
        }
        return $props;
    }
    
    /**
     * 
     * @param \Bpi\RestMediaTypeBundle\Element\Hypermedia $controls
     * @return \Bpi\RestMediaTypeBundle\Element\Entity
     */
    public function setHypermedia(Hypermedia $controls)
    {
        $this->hypermedia = $controls;
        return $this;
    }
}

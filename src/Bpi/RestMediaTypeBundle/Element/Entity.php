<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\SerializerBundle\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\RestMediaTypeBundle\Element\Link;
use Bpi\RestMediaTypeBundle\Element\Scope\HasLinks;
use Bpi\RestMediaTypeBundle\Document;

/**
 * @Serializer\XmlRoot("entity")
 */
class Entity implements HasLinks
{
    /**
     * @Serializer\Exclude
     */
    protected $document;

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
        $this->properties[$property->getName()] = $property;
    }

    public function addProperties(array $properties)
    {
        foreach ($properties as $property)
            $this->addProperty($property);
    }

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

    public function attach(Document $document)
    {
        $this->document = $document;
    }

    public function isOwner(Document $document)
    {
        return spl_object_hash($this->document) == spl_object_hash($document);
    }

    public function addChildEntity(Entity $entity)
    {
        $this->document->setCursorOnEntity($entity);
        $this->entities[$entity->getName()] = $entity;
    }

    public function getChildEntity($name)
    {
        return $this->entities[$name];
    }

    /**
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
}

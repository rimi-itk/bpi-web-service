<?php

namespace Bpi\RestMediaTypeBundle;

use Doctrine\ORM\Mapping\ElementCollection;
use JMS\Serializer\Annotation as Serializer;

use Bpi\RestMediaTypeBundle\Element\Collection;
use Bpi\RestMediaTypeBundle\Element\Entity as Entity;
use Bpi\RestMediaTypeBundle\Element\Users;
use Bpi\RestMediaTypeBundle\Element\Property as GenericProperty;
use Bpi\RestMediaTypeBundle\Property;
use Bpi\RestMediaTypeBundle\Element\Link;

/**
 * @Serializer\XmlRoot("bpi")
 */
class Document extends XmlResponse
{
    /**
     * @Serializer\Exclude
     * @var \Bpi\RestMediaTypeBundle\Element\Entity current entity
     */
    protected $current_entity;


    /**
     * @Serializer\XmlList(inline=true, entry="item")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Entity>")
     */
    protected $entities = [];

    /**
     * @Serializer\Type("Bpi\RestMediaTypeBundle\Element\Collection")
     */
    protected $collection;


    /**
     * Call the callback on each entity
     *
     * @param callback $callback
     */
    public function walkEntities($callback)
    {
        array_walk($this->entities, $callback);
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
     *
     * @return Entity
     * @throws \LogicException
     */
    public function getEntity($name)
    {
        foreach ($this->entities as $entity) {
            if ($entity->getName() == $name) {
                return $entity;
            }
        }

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
     * @param string $type
     * @param string $name
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Entity
     */
    public function createRootEntity($type, $name = null)
    {
        $entity = new Entity($type, $name);
        $entity->attach($this);
        $this->entities[] = $entity;
        $this->setCursorOnEntity($entity);

        return $entity;
    }

    /**
     * Create new entity instance
     *
     * @param string $type
     * @param string $name
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Entity
     */
    public function createEntity($type, $name = null, $class = "Entity")
    {
        $class = "\\Bpi\\RestMediaTypeBundle\\Element\\{$class}";
        $entity = new $class($type, $name);
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
        $this->addEntity($entity);
    }

    /**
     * Prepend entity to the beginning of document
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Entity $entity
     */
    public function prependEntity(Entity $entity)
    {
        array_unshift($this->entities, $entity);
        $this->addEntity($entity);
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Entity $entity
     */
    protected function addEntity(Entity $entity)
    {
        $this->setCursorOnEntity($entity);
        $entity->attach($this);
    }

    /**
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Hypermedia
     */
    public function createHypermediaSection()
    {
        return new Element\Hypermedia();
    }

    /**
     * @return \Bpi\RestMediaTypeBundle\Element\Tags
     */
    public function createTagsSection()
    {
        return new Element\Tags();
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @param string $value
     * @param string $title
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Property
     */
    public function createProperty($name, $type, $value, $title = '')
    {
        switch ($type) {
            case 'dateTime':
                return new Property\DateTime($type, $name, $value, $title);
                break;
            case 'number':
                return new Property\Number($type, $name, $value, $title);
                break;
            default:
                return new GenericProperty($type, $name, $value, $title);
        }
    }

    /**
     * Create a link
     *
     * @param string $rel
     * @param string $href
     * @param string $title
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Link
     */
    public function createLink($rel, $href, $title = '')
    {
        return new Link($rel, $href, $title);
    }

    /**
     *
     * @param string $rel
     * @param string $href
     * @param array $params
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Query
     */
    public function createQuery($rel, $href, array $params, $title = null)
    {
        $query = new Element\Query($rel, $href, $title);
        foreach ($params as $param) {
            $query->addParam(is_object($param) ? $param : $this->createQueryParameter($param));
        }

        return $query;
    }

    /**
     *
     * @param string $name
     * @param array $attributes
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Param
     */
    public function createQueryParameter($name)
    {
        return new Element\Param($name);
    }

    /**
     *
     * @param string $rel
     * @param string $href
     * @param array $fields
     * @param string $title
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Template
     */
    public function createTemplate($rel, $href, $title = null)
    {
        return new Element\Template($rel, $href, $title);
    }

    /**
     * Get last used entity
     *
     * @return \Bpi\RestMediaTypeBundle\Element\Entity
     * @throw \RuntimeException
     */
    public function currentEntity()
    {
        if (!is_object($this->current_entity)) {
            throw new \RuntimeException('There is no current entities yet');
        }

        return $this->current_entity;
    }

    /**
     * Set internal pointer to entity
     *
     * @see currentEntity()
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Entity $entity
     */
    public function setCursorOnEntity(Entity $entity)
    {
        if ($entity->isOwner($this)) {
            $this->current_entity = $entity;
        }
    }

    /**
     * Dump document as recursive array
     *
     * @return array
     */
    public function dump()
    {
        $dump = [];
        foreach ($this->getEntities() as $entity) {
            $entity_dump = [];
            $entity->walk(
                function ($property) use (&$entity_dump) {
                    // @todo in some cases entity can have many properties with the same name
                    $entity_dump[$property->getName()] = $property->getValue();
                }
            );
            $dump[$entity->getName()] = $entity_dump;
        }

        return $dump;
    }
}

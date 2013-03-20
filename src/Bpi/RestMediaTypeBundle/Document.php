<?php
namespace Bpi\RestMediaTypeBundle;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as Router;

use Bpi\RestMediaTypeBundle\Element\Collection;
use Bpi\RestMediaTypeBundle\Element\Entity;
use Bpi\RestMediaTypeBundle\Element\Property as GenericProperty;
use Bpi\RestMediaTypeBundle\Property;
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
     * @Serializer\Exclude
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $router;

    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $version;

    /**
     * @Serializer\XmlList(inline=true, entry="entity")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Entity>")
     */
    protected $entities = array();

    /**
     * @Serializer\Type("Bpi\RestMediaTypeBundle\Element\Collection")
     */
    protected $collection;

    public function __construct()
    {
        $this->version = '0.1';
    }

    /**
     * Inject router dependency
     *
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string  $name       The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     *
     * @throws RouteNotFoundException if route doesn't exist
     */
    public function generateRoute($name, $parameters = array(), $absolute = false)
    {
        return $this->router->generate($name, $parameters, $absolute);
    }

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
        switch($type) {
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

    /**
     * Dump document as recursive array
     *
     * @return array
     */
    public function dump()
    {
        $dump = array();
        foreach($this->getEntities() as $entity)
        {
            $entity_dump = array();
            $entity->walk(function($property) use (&$entity_dump) {
                // @todo in some cases entity can have many properties with the same name
                $entity_dump[$property->getName()] = $property->getValue();
            });
            $dump[$entity->getName()] = $entity_dump;
        }
        return $dump;
    }
}

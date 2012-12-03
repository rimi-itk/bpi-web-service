<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;
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
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * @Serializer\XmlList(entry="entity")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Entity>")
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

    public function getItems()
    {
        return $this->items;
    }
}

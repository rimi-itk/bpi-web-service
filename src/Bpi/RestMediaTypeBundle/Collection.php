<?php
namespace Bpi\RestMediaTypeBundle;

use Bpi\RestMediaTypeBundle\Element\Item;
use Bpi\RestMediaTypeBundle\Element\Facet;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("collection")
 */
class Collection extends XmlResponse
{
    /**
     * @var int
     * @Serializer\XmlAttribute
     */
    public $total;

    /**
     * @var int
     * @Serializer\XmlAttribute
     */
    public $offset;

    /**
     * @var int
     * @Serializer\XmlAttribute
     */
    public $amount;

    /**
     * @Serializer\XmlList(inline=true, entry="item")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Item>")
     */
    public $items;

    /**
     * @Serializer\XmlList(inline=true, entry="facet")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Facet>")
     */
    public $facets = array();

    /**
     * Set $total.
     *
     * @param int $total
     *
     * @return Collection
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Set $offset.
     *
     * @param int $offset
     *
     * @return Collection
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Set $amount.
     *
     * @param int $amount
     *
     * @return Collection
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Add an Item to the Collection.
     *
     * @param Item $item
     *
     * @return Collection
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Add a Facet to the Collection.
     *
     * @param Facet $facet
     *
     * @return Collection
     */
    public function addFacet(Facet $facet)
    {
        $this->facets[] = $facet;

        return $this;
    }
}

<?php

namespace Bpi\ApiBundle\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Bpi\ApiBundle\Domain\Entity\Facet
 */
class Facet
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $nodeId
     */
    protected $nodeId;

    /**
     * @var collection $facetData
     */
    protected $facetData;


    public function __construct()
    {
        $this->facetData = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nodeId
     *
     * @param string $nodeId
     *
     * @return self
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;

        return $this;
    }

    /**
     * Get nodeId
     *
     * @return string $nodeId
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * Set facetData
     *
     * @param collection $facetData
     *
     * @return self
     */
    public function setFacetData($facetData)
    {
        $this->facetData = $facetData;

        return $this;
    }

    /**
     * Get facetData
     *
     * @return collection $facetData
     */
    public function getFacetData()
    {
        return $this->facetData;
    }
}

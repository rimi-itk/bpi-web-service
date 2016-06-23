<?php

namespace Bpi\ApiBundle\Domain\Entity;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Bpi\ApiBundle\Domain\Entity\ChannelFacet
 */
class ChannelFacet
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $channelId
     */
    protected $channelId;

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
     * Set channelId
     *
     * @param string $channelId
     * @return self
     */
    public function setChannelId($channelId)
    {
        $this->channelId = $channelId;
        return $this;
    }

    /**
     * Get channelId
     *
     * @return string $channelId
     */
    public function getChannelId()
    {
        return $this->channelId;
    }

    /**
     * Set facetData
     *
     * @param collection $facetData
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

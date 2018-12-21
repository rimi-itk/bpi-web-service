<?php
namespace Bpi\ApiBundle\Domain\Entity;

/**
 * Bpi\ApiBundle\Domain\Entity\ChannelFacet
 */
class ChannelFacet
{
    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var string $channelId
     */
    protected $channelId;

    /**
     * @var array $facetData
     */
    protected $facetData;

    /**
     * Create a new ChannelFacet.
     */
    public function __construct()
    {
    }

    /**
     * Get id.
     *
     * @return string $id
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
     * @param array $facetData
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
     * @return array $facetData
     */
    public function getFacetData()
    {
        return $this->facetData;
    }
}

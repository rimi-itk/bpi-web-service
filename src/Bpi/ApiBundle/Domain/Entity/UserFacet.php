<?php

namespace Bpi\ApiBundle\Domain\Entity;

/**
 * Bpi\ApiBundle\Domain\Entity\UserFacet
 */
class UserFacet
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $userId
     */
    protected $userId;

    /**
     * @var array $facetData
     */
    protected $facetData;

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param string $userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set facetData
     *
     * @param array $facetData
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
     * @return array $facetData
     */
    public function getFacetData()
    {
        return $this->facetData;
    }
}

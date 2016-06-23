<?php

namespace Bpi\ApiBundle\Domain\Entity;
use Doctrine\Common\Collections\ArrayCollection;


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
     * Set userId
     *
     * @param string $userId
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

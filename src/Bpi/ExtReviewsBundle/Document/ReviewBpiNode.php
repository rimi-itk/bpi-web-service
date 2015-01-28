<?php

namespace Bpi\ExtReviewsBundle\Document;



/**
 * Bpi\ExtReviewsBundle\Document\ReviewBpiNode
 */
class ReviewBpiNode
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $reviewUri
     */
    protected $reviewUri;

    /**
     * @var string $bpiId
     */
    protected $bpiId;


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
     * Set reviewUri
     *
     * @param string $reviewUri
     * @return self
     */
    public function setReviewUri($reviewUri)
    {
        $this->reviewUri = $reviewUri;
        return $this;
    }

    /**
     * Get reviewUri
     *
     * @return string $reviewUri
     */
    public function getReviewUri()
    {
        return $this->reviewUri;
    }

    /**
     * Set bpiId
     *
     * @param string $bpiId
     * @return self
     */
    public function setBpiId($bpiId)
    {
        $this->bpiId = $bpiId;
        return $this;
    }

    /**
     * Get bpiId
     *
     * @return string $bpiId
     */
    public function getBpiId()
    {
        return $this->bpiId;
    }
}
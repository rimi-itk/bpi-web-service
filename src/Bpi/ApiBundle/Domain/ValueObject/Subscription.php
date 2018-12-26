<?php

namespace Bpi\ApiBundle\Domain\ValueObject;

/**
 * Bpi\ApiBundle\Domain\ValueObject\Subscription
 */
class Subscription
{
    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var date $lastViewed
     */
    protected $lastViewed;

    /**
     * @var string $filter
     */
    protected $filter;

    /**
     * Set title
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set lastViewed
     *
     * @param \DateTime $lastViewed
     *
     * @return self
     */
    public function setLastViewed($lastViewed)
    {
        $this->lastViewed = $lastViewed;

        return $this;
    }

    /**
     * Get lastViewed
     *
     * @return date $lastViewed
     */
    public function getLastViewed()
    {
        return $this->lastViewed;
    }

    /**
     * Set filter
     *
     * @param string $filter
     *
     * @return self
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return string $filter
     */
    public function getFilter()
    {
        return $this->filter;
    }
}

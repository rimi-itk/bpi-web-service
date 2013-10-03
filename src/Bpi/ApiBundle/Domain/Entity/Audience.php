<?php
namespace Bpi\ApiBundle\Domain\Entity;

class Audience
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $audience;

    /**
     * @param string $audience
     */
    public function __construct($audience = null)
    {
        $this->setAudience($audience);
    }

    /**
     * Set audience.
     *
     * @param string $audience
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;
    }

    /**
     * Get audience name.
     *
     * @return string
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * Get audience ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}

<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

class Copyleft implements IValueObject
{
    protected $licence;

    /**
     *
     * @param string $licence
     */
    public function __construct($licence)
    {
        $this->licence = $licence;
    }

    /**
     *
     * @param  \Bpi\ApiBundle\Domain\ValueObject\Copyleft $copyleft
     * @return boolean
     */
    public function equals(IValueObject $copyleft)
    {
        return $this->licence == $copyleft->licence;
    }

    public function __toString()
    {
        return $this->licence;
    }
}

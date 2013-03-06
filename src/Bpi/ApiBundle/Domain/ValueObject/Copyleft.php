<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

class Copyleft implements IValueObject
{
    protected $copyrighers = array();

    /**
     * Name of copyleft owner
     *
     * @param string $name
     * @param boolean $direction true for add item to the end, otherwise prepend
     */
    public function addCopyrigher($name, $direction = true)
    {
        $direction ? $this->copyrighers[] = $name : array_unshift($this->copyrighers, $name);
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
        return 'Originally published by ' . implode(', ', $this->copyrighers) . '.';
    }
}

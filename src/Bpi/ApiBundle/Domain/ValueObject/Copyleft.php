<?php

namespace Bpi\ApiBundle\Domain\ValueObject;

class Copyleft implements IValueObject
{
    protected $copyrighters = [];

    /**
     * Name of copyleft owner
     *
     * @param string $name
     * @param boolean $append true for add item to the end, otherwise prepend
     */
    public function addCopyrigher($name, $append = true)
    {
        $append ? array_push($this->copyrighters, $name) : array_unshift($this->copyrighters, $name);
    }

    /**
     *
     * @param  \Bpi\ApiBundle\Domain\ValueObject\Copyleft $copyleft
     *
     * @return boolean
     */
    public function equals(IValueObject $copyleft)
    {
        return $this->copyrighters == $copyleft->copyrighters;
    }

    public function __toString()
    {
        //TODO: Temporary hardcoded translation.
        return 'Udgivet af '.implode(', ', $this->copyrighters).'.';
    }

    /**
     * Set copyrighters
     *
     * @param hash $copyrighters
     *
     * @return self
     */
    public function setCopyrighters($copyrighters)
    {
        $this->copyrighters = $copyrighters;

        return $this;
    }

    /**
     * Get copyrighters
     *
     * @return hash $copyrighters
     */
    public function getCopyrighters()
    {
        return $this->copyrighters;
    }
}

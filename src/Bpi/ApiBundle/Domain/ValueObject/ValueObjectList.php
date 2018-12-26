<?php

namespace Bpi\ApiBundle\Domain\ValueObject;

use Doctrine\Common\Collections\ArrayCollection;

class ValueObjectList extends ArrayCollection
{
    public function contains($value_object)
    {
        $this->rewind();
        while ($this->valid()) {
            if ($value_object->equals($this->current())) {
                return true;
            }

            $this->next();
        }
    }
}

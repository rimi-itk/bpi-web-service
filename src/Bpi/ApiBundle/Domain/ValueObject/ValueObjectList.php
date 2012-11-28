<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

use Doctrine\Common\Collections\ArrayCollection;

class ValueObjectList extends ArrayCollection
{
    public function contains(IValueObject $vo)
    {
        $this->rewind();
        while($this->valid()) {
            if ($vo->equals($this->current()))
                return true;

            $this->next();
        }
    }
}

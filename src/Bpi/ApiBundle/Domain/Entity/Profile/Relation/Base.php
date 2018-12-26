<?php

namespace Bpi\ApiBundle\Domain\Entity\Profile\Relation;

class Base implements IRelation
{
    protected $name;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }
}

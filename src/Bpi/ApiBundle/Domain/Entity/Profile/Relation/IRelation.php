<?php

namespace Bpi\ApiBundle\Domain\Entity\Profile\Relation;

interface IRelation
{
    public function __construct($value);

    public function value();
}

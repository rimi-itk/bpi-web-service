<?php

namespace Bpi\ApiBundle\Domain\ValueObject;

interface IValueObject
{
    public function equals(IValueObject $vo);
}

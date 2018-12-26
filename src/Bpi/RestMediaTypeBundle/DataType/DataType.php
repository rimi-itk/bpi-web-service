<?php

namespace Bpi\RestMediaTypeBundle\DataType;

interface DataType
{
    public function __construct($value);

    public function value();
}

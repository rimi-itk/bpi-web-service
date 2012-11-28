<?php
namespace Bpi\RestMediaTypeBundle\DataType;

class String implements DataType
{
    protected $value;

    public function __construct($value)
    {
        $this->value = (string) $value;
    }

    public function value()
    {
        return (string) $this->value;
    }
}

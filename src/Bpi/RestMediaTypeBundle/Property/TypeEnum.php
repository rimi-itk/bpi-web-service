<?php

namespace Bpi\RestMediaTypeBundle\Property;

class TypeEnum
{
    const STRING = 'string';
    const NUMBER = 'number';
    const DATETIME = 'dateTime';
    const ENTITY = 'entity';

    protected $enum = [
        self::STRING,
        self::NUMBER,
        self::DATETIME,
        self::ENTITY,
    ];

    protected $selected;

    public function __construct($type)
    {
        if (!in_array($type, $this->enum)) {
            throw new \InvalidArgumentException('Value '.$type.' not exists in Enumeration.');
        }

        $this->selected = $type;
    }

    public function toString()
    {
        return $this->selected;
    }
}

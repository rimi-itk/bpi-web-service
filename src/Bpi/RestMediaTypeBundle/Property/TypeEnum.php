<?php
namespace Bpi\RestMediaTypeBundle\Property;

class TypeEnum
{
    const string = 'string';
    const number = 'number';
    const dateTime = 'dateTime';
    const entity = 'entity';
    const assets = 'assets';

    protected $enum = array(
        self::string,
        self::number,
        self::dateTime,
        self::entity,
        self::assets
    );

    protected $selected;

    public function __construct($type)
    {
        if (!in_array($type, $this->enum))
            throw new \InvalidArgumentException('Value '.$type.' not exists in Enumiration');

        $this->selected = $type;
    }

    public function toString()
    {
        return $this->selected;
    }
}

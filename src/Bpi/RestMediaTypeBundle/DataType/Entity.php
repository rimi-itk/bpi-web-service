<?php
namespace Bpi\RestMediaTypeBundle\DataType;

use Bpi\RestMediaTypeBundle\Element\Entity as EntityElement;

class Entity implements DataType
{
    /**
     * @var array
     */
    protected $value;

    /**
     * @param \Bpi\RestMediaTypeBundle\Element\Entity|array $value
     * @throws \InvalidArgumentException
     */
    public function __construct($value)
    {

        if (!is_array($value))
            $value = array($value);

        foreach($value as $item)
            if ( !($item instanceof EntityElement))
                throw new \InvalidArgumentException('Given value is not instance of \Bpi\RestMediaTypeBundle\Element\Entity' );

        $this->value = $value;
    }

    /**
     * @return array
     */
    public function value()
    {
        return $this->value;
    }
}

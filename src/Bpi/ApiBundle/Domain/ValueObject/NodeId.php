<?php

namespace Bpi\ApiBundle\Domain\ValueObject;

class NodeId
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function id()
    {
        return $this->id;
    }
}

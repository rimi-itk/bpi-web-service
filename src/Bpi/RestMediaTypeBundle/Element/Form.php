<?php

namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("form")
 */
class Form
{
    protected $action;

    protected $method = 'get';

    protected $id;

    public function __construct($action, $method)
    {
        $this->action = $action;
        $this->method = $method;
    }
}

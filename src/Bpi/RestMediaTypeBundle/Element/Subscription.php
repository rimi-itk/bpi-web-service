<?php
/**
 * @file
 *  Serialization class for Subscription entity.
 */

namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Subscription
 *
 * @package Bpi\RestMediaTypeBundle\Element
 * @Serializer\XmlRoot("Subscription")
 */
class Subscription
{
    /**
     * @Serializer\Type("string")
     */
    protected $title;

    /**
     * @Serializer\Type("string")
     */
    protected $lastViewed;

    /**
     * @Serializer\Type("string")
     */
    protected $filter;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->title = $data->getTitle();
        $this->lastViewed = $data->getLastViewed();
        $this->filter = $data->getFilter();
    }
}

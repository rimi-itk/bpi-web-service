<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 09.07.2015
 * Time: 15:48
 */

namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Tags
 * @package Bpi\RestMediaTypeBundle\Element
 * @Serializer\XmlRoot("tags")
 */
class Tags
{
    /**
     * @Serializer\XmlList(inline=true, entry="tag")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Tag>")
     */
    protected $tags;

    public function __construct()
    {
        $this->tags = array();
    }

    /**
     * @param \Bpi\RestMediaTypeBundle\Element\Tag $tag
     * @return \Bpi\RestMediaTypeBundle\Element\Tags
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        return $this;
    }
}

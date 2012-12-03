<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("link")
 */
class Link
{
    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $rel;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $href;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $title;

    /**
     *
     * @param string $rel relation name
     * @param string $href URI
     * @param string $title
     */
    public function __construct($rel, $href, $title = null)
    {
        $this->rel = $rel;
        $this->href = $href;
        $this->title = $title;
    }

    public function getRelationName()
    {
        return $this->rel;
    }

    public function getUri()
    {
        return $this->href;
    }

    public function getTitle()
    {
        return $this->title;
    }
}

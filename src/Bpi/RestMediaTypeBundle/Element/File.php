<?php

namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("file")
 */
class File
{
    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $path;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $name;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $title;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $alt;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $extension;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $external;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $type;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $width;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlAttribute
     */
    protected $height;

    /**
     *
     * @param array $name
     */
    public function __construct($data)
    {
        $this->name = $data->getName();
        $this->path = $data->getPath();
        $this->name = $data->getName();
        $this->title = $data->getTitle();
        $this->alt = $data->getTitle();
        $this->extension = $data->getExtension();
        $this->external = $data->getExternal();
        $this->type = $data->getType();
        $this->width = $data->getWidth();
        $this->height = $data->getHeight();
    }
}

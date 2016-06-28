<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Property\TypeEnum;

/**
 * @Serializer\XmlRoot("term")
 */
class FacetTerm
{
    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * @Serializer\XmlValue
     * @Serializer\Type("string")
     */
    protected $value;

    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $title;

    /**
     *
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param string $title
     */
    public function __construct($name, $value, $title = '')
    {
        $this->name = $name;
        $this->value = $value;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}

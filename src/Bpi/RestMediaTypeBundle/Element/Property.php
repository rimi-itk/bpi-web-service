<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Property\TypeEnum;

/**
 * @Serializer\XmlRoot("property")
 */
class Property
{
    const TYPE_NUMBER = 'number';
    const TYPE_STRING = 'string';
    const TYPE_DATETIME = 'dateTime';

    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    private $type;

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
    public function __construct($type, $name, $value, $title = '')
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
        $this->title = $title;
        $this->normalizeValue();
    }

    /**
     * Set PHP value type to correspond formal type
     *
     * @Serializer\PostDeserialize
     */
    protected function normalizeValue()
    {
        if (function_exists('is_'.$this->type)) {
            // WTF??? No "@" allowed!!!
            @settype($this->value, $this->type);
        }
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Property\TypeEnum $enum
     */
    protected function setType(TypeEnum $enum)
    {
        $this->type = $enum->toString();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function typeOf($type)
    {
        /** @todo value may not correspond to the type */
        return $this->type == $type;
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

<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("template")
 */
class Template
{
    /**
     * @Serializer\XmlList(entry="field")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Template\Field>")
     */
    protected $fields;

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
     * @param string $rel
     * @param string $href
     * @param string $title
     */
    public function __construct($rel, $href, $title = null)
    {
        $this->rel = $rel;
        $this->href = $href;
        $this->title = $title;
    }

    /**
     * 
     * @param \Bpi\RestMediaTypeBundle\Element\Template\Field $field
     * @return \Bpi\RestMediaTypeBundle\Element\Template
     */
    public function addField(Template\Field $field)
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     *
     * @param string $name
     * @return \Bpi\RestMediaTypeBundle\Element\Template\Field
     */
    public function createField($name)
    {
      $this->addField($field = new Template\Field($name));
      return $field;
    }
}

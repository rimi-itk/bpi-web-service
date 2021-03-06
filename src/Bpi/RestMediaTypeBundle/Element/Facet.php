<?php

namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("facet")
 */
class Facet
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
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $title;

    /**
     * @Serializer\XmlList(inline=true, entry="term")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\FacetTerm>")
     */
    protected $terms;

    /**
     * @param string $type
     * @param string $name
     * @param string $title
     */
    public function __construct($type, $name, $title = '')
    {
        $this->type = $type;
        $this->name = $name;
        $this->title = $title;
    }

    /**
     * Add a term to the facet.
     *
     * @param FacetTerm $term
     */
    public function addTerm(FacetTerm $term)
    {
        $this->terms[] = $term;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getTerms()
    {
        return $this->terms;
    }
}

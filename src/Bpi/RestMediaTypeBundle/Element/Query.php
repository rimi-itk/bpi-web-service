<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("query")
 */
class Query extends Link
{
    /**
     * @Serializer\XmlList(entry="param")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Param>")
     */
    protected $params;

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
     * @param \Bpi\RestMediaTypeBundle\Element\Param $param
     * @return \Bpi\RestMediaTypeBundle\Element\Query
     */
    public function addParam(Param $param)
    {
        $this->params[] = $param;
        return $this;
    }
}

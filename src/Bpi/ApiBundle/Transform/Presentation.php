<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\XmlResponse;

/**
 * Transforms domain model into presentation document
 */
class Presentation
{
    protected $doc;

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\XmlResponse $doc
     */
    public function __construct(XmlResponse $doc = null)
    {
        $this->doc = $doc;
    }


    /**
     * @param XmlResponse $d
     */
    public function setDoc(XmlResponse $d) {
        $this->doc = $d;
    }

    /**
     *
     * @param \Bpi\ApiBundle\Transform\IPresentable $model
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function transform(IPresentable $model)
    {
        $document = clone $this->doc;
        $model->transform($document);
        return $document;
    }

    /**
     *
     * @param array $models
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function transformMany($models)
    {
        $document = clone $this->doc;
        foreach ($models as $model) {
            $model->transform($document);
        }
        return $document;
    }
}

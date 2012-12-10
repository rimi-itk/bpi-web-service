<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;

/**
 * Transforms domain model into presentation document
 */
class Presentation
{
    protected $doc;

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Document $doc
     */
    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    /**
     *
     * @param \Bpi\ApiBundle\Transform\IPresentable $model
     * @return \Bpi\RestMediaTypeBundle\Document
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
     * @return \Bpi\RestMediaTypeBundle\Document
     */
    public function transformMany($models)
    {
        $document = clone $this->doc;
        foreach ($models as $model)
            $model->transform($document);
        return $document;
    }
}

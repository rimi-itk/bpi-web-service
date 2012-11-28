<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;

class Presentation
{
    /**
     *
     * @param \Bpi\ApiBundle\Transform\IPresentable $model
     * @return \Bpi\RestMediaTypeBundle\Document
     */
    public static function transform(IPresentable $model)
    {
        $document = new Document();
        $model->transform($document);
        return $document;
    }

    /**
     *
     * @param array $models
     * @return \Bpi\RestMediaTypeBundle\Document
     */
    public static function transformMany($models)
    {
        $document = new Document();
        foreach ($models as $model)
            $model->transform($document);
        return $document;
    }
}

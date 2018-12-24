<?php

namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;

interface IExtractor
{
    /**
     *
     * @param \Bpi\ApiBundle\Transform\Extractor\Document $doc
     */
    public function __construct(Document $doc);

    /**
     * Extract domain model from presentation
     */
    public function extract();
}

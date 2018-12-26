<?php

namespace Bpi\ApiBundle\Transform;

use Bpi\RestMediaTypeBundle\XmlResponse;

interface IPresentable
{
    /**
     * Transform Domain model into Presenation BPI document
     *
     * @param \Bpi\RestMediaTypeBundle\XmlResponse $document
     */
    public function transform(XmlResponse $xml);
}

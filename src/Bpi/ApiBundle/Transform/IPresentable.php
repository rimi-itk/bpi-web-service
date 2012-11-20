<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\RestMediaTypeBundle\Document;

interface IPresentable
{
	public function transform(Document $document);
}

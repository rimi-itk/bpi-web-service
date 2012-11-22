<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\RestMediaTypeBundle\Document;

interface IPresentable
{
	/**
	 * Transform Domain model into Presenation BPI document
	 * 
	 * @param \Bpi\RestMediaTypeBundle\Document $document
	 */
	public function transform(Document $document);
}

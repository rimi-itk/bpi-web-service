<?php
namespace Bpi\RestMediaTypeBundle\Element\Scope;

use Bpi\RestMediaTypeBundle\Element\Link;

interface HasLinks
{
	/**
	 * @param \Bpi\RestMediaTypeBundle\Element\Link $link
	 * @return self
	 */
	public function addLink(Link $link);
}
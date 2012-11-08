<?php
namespace Bpi\RestMediaTypeBundle\Element\Scope;

use Bpi\RestMediaTypeBundle\Element\Link;

interface HasLinks
{
	public function addLink(Link $link);
}
<?php
namespace Bpi\ApiBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Relation
{
	/**
	 * @MongoDB\String
	 */
	protected $type;
	
	/**
	 * @MongoDB\String
	 */
	protected $reference;
}
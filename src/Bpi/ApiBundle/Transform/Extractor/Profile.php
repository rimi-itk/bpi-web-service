<?php
namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\Profile as DomainProfile;
use Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;

class Profile implements IExtractor
{
	/**
	 * @var Document
	 */
	protected $doc;
	
	public function __construct(Document $doc)
	{
		$this->doc = $doc;
	}
	
	public function extract()
	{
		$entity = $this->doc->getEntity('profile');
		return new DomainProfile(new Taxonomy(
			new Audience($entity->property('audience')->getValue()), 
			new Category($entity->property('category')->getValue())
		));
	}
}
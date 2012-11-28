<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Domain\ValueObject\Author;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;

/**
 * Remote resource like article, news item, etc
 */
class Resource implements IPresentable
{
	protected $title;
	
	protected $body;
	
	protected $teaser;
	
	protected $ctime;
	
	protected $type = 'article';
	
	public function __construct(
		$title,
		$body,
		$teaser,
		\DateTime $ctime
	)
	{
		$this->title = $title;
		$this->body = $body;
		$this->teaser = $teaser;
		$this->ctime = $ctime;
	}
	
	/**
	 * Calculate similarity of resources by checking body contents
	 * 
	 * @param Resource $resource
	 * @return boolean
	 */
	public function isSimilar(Resource $resource)
	{
		if ($this->body == $resource->body)
			return true;
		
		similar_text(strip_tags($this->body), strip_tags($resource->body), $similarity);
		if ($similarity > 50)
			return true;
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function transform(Document $document)
	{
		$document->currentEntity()->addChildEntity(
			$entity = $document->createEntity('resource')
		);
		
		$entity->addProperty($document->createProperty('title', 'string', $this->title));
		$entity->addProperty($document->createProperty('body', 'string', $this->body));
		$entity->addProperty($document->createProperty('teaser', 'string', $this->teaser));
		$entity->addProperty($document->createProperty('ctime', 'dateTime', $this->ctime));
		$entity->addProperty($document->createProperty('type', 'string', $this->type));
	}
	
	/**
	 * 
	 * @param \Bpi\ApiBundle\Domain\Entity\Resource $resource
	 * @param string $field
	 * @param int $order 1=asc, -1=desc
	 * @return int see strcmp PHP function
	 */
	public function compare(Resource $resource, $field, $order = 1)
	{
		if (stristr($field, '.'))
		{
			list($local_field, $child_field) = explode(".", $field, 2);
			return $this->$local_field->compare($resource->$local_field, $child_field, $order);
		}
		
		$cmp = new Comparator($this->$field, $resource->$field, $order);
		return $cmp->getResult();
	}
}
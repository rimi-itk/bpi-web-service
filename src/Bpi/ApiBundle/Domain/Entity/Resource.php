<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Domain\ValueObject\Author;

/**
 * Remote resource like article, news item, etc
 */
class Resource
{
	protected $title;
	
	protected $body;
	
	protected $user_id;
	
	protected $teaser;
	
	protected $ctime;
	
	/**
	 * @var Author
	 */
	protected $author; //?
	
	protected $id; // !!
	
	/**
	 * @var \Bpi\ApiBundle\Domain\Aggregate\Resource\Types\Type
	 */
	protected $type;
	
	public function __construct(
		$title,
		$body,
		$user_id, 
		$teaser,
		\DateTime $ctime
	)
	{
		$this->title = $title;
		$this->body = $body;
		$this->user_id = $user_id;
		$this->teaser = $teaser;
		$this->ctime = $ctime;
	}
	
	public function relateWith($name, $value)
	{
		
	}
}
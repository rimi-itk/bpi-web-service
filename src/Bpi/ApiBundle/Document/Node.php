<?php
namespace Bpi\ApiBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Node
{

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Timestamp
	 */
	protected $ctime;

	/**
	 * @MongoDB\Timestamp
	 */
	protected $mtime;

	/**
	 * @MongoDB\String
	 */
	protected $comment;

	/**
	 * @MongoDB\String
	 */
	protected $path;

	/**
	 * @MongoDB\String
	 */
	protected $type;

	/**
	 * @MongoDB\EmbedOne(targetDocument="Bpi\ApiBundle\Document\Resource")
	 */
	protected $resource;

	/**
	 * Get id
	 *
	 * @return id $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set ctime
	 *
	 * @param timestamp $ctime
	 * @return Node
	 */
	public function setCtime($ctime)
	{
		$this->ctime = $ctime;
		return $this;
	}

	/**
	 * Get ctime
	 *
	 * @return timestamp $ctime
	 */
	public function getCtime()
	{
		return $this->ctime;
	}

	/**
	 * Set mtime
	 *
	 * @param timestamp $mtime
	 * @return Node
	 */
	public function setMtime($mtime)
	{
		$this->mtime = $mtime;
		return $this;
	}

	/**
	 * Get mtime
	 *
	 * @return timestamp $mtime
	 */
	public function getMtime()
	{
		return $this->mtime;
	}

	/**
	 * Set comment
	 *
	 * @param string $comment
	 * @return Node
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}

	/**
	 * Get comment
	 *
	 * @return string $comment
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * Set path
	 *
	 * @param string $path
	 * @return Node
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * Get path
	 *
	 * @return string $path
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 * @return Node
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string $type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set resource
	 *
	 * @param Bpi\ApiBundle\Document\Resource $resource
	 * @return Node
	 */
	public function setResource(\Bpi\ApiBundle\Document\Resource $resource)
	{
		$this->resource = $resource;
		return $this;
	}

	/**
	 * Get resource
	 *
	 * @return Bpi\ApiBundle\Document\Resource $resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

}

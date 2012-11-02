<?php
namespace Bpi\ApiBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Resource
{
	/**
	 * @MongoDB\Timestamp
	 */
	protected $ctime;

	/**
	 * @MongoDB\String
	 */
	protected $title;
	
	/**
	 * @MongoDB\String
	 */
	protected  $teaser;
	
	/**
	 * @MongoDB\String
	 */
	protected  $body;
	
	/**
	 * @MongoDB\String
	 */
	protected $author;
	
	/**
	 * @MongoDB\Int
	 */
	protected $user_id;
	
	/**
	 * @MongoDB\EmbedMany(targetDocument="Bpi\ApiBundle\Document\Relation")
	 */
	protected $relations;


//		"attachments": [
//			{
//				"_ref_id": "GridFS.ObjectId()"
//			}
//		],
	

    /**
     * Set ctime
     *
     * @param timestamp $ctime
     * @return Resource
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
     * Set title
     *
     * @param string $title
     * @return Resource
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Resource
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Get text
     *
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return Resource
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get author
     *
     * @return string $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set user_id
     *
     * @param int $userId
     * @return Resource
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
        return $this;
    }

    /**
     * Get user_id
     *
     * @return int $userId
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}

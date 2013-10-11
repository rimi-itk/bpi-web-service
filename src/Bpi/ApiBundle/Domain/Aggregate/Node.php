<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Category;
use Bpi\ApiBundle\Domain\Entity\Audience;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;
use Gaufrette\File;

class Node implements IPresentable
{
    protected $id;
    protected $ctime;
    protected $mtime;

    protected $path;
    protected $parent;
    protected $level = 0;
    protected $lock_time;

    protected $author;
    protected $resource;
    protected $profile;
    protected $params;

    protected $category;
    protected $audience;

    protected $deleted = false;

    public function __construct(
        Author $author = null,
        Resource $resource = null,
        Profile $profile = null,
        $category = null,
        $audience = null,
        Params $params = null
    ) {
        $this->author = $author;
        $this->resource = $resource;
        $this->profile = $profile;
        $this->params = $params;
        $this->category = $category;
        $this->audience = $audience;

        $this->markTimes();
    }

    protected function markTimes()
    {
        $this->mtime = $this->ctime = new \DateTime('now');
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Compare two instances by field. Need by sorting
     *
     * @param \Bpi\ApiBundle\Domain\Aggregate\Node $node
     * @param string $field can be compound like profile.taxonomy.category
     * @param int $order 1=asc, -1=desc
     * @return int see strcmp PHP function
     */
    public function compare(Node $node, $field, $order = 1)
    {
        if (stristr($field, '.')) {
            list($local_field, $child_field) = explode(".", $field, 2);
            return $this->$local_field->compare($node->$local_field, $child_field, $order);
        }

        $cmp = new Comparator($this->$field, $node->$field, $order);
        return $cmp->getResult();
    }

    /**
     * Calculate similarity of resources
     *
     * @param Resource $resource
     * @return boolean
     */
    protected function isSimilarResource(Resource $resource)
    {
        return $this->resource->isSimilar($resource);
    }

    /**
     * Create new revision of current node
     *
     * @param Resource $resource
     * @return Node
     */
    public function createRevision(Author $author, Resource $resource, Params $params)
    {
        $builder = new \Bpi\ApiBundle\Domain\Factory\NodeBuilder;
        $node = $builder
            ->author($author)
            ->profile($this->profile)
            ->resource($resource)
            ->params($params)
            ->build()
        ;

        $node->parent = $this;
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Document $document)
    {
        $entity = $document->createEntity('entity');

        $entity->addProperty($document->createProperty(
            'id',
            'string',
            $this->getId()
        ));

        $entity->addProperty($document->createProperty(
            'pushed',
            'dateTime',
            $this->ctime
        ));

        $entity->addProperty($document->createProperty(
            'editable',
            'boolean',
            (int) $this->params
                ->filter(function($e){ if ($e instanceof Editable) return true; })
                ->first()
                ->isPositive()
        ));

        $document->appendEntity($entity);

        $document->setCursorOnEntity($entity);
        $this->author->transform($document);

        $entity->addProperty(
            $document->createProperty(
                'category',
                'string',
                $this->getCategory()->getCategory()
            )
        );
        $entity->addProperty(
            $document->createProperty(
                'audience',
                'string',
                $this->getAudience()->getAudience()
            )
        );

        $this->profile->transform($document);
        $this->resource->transform($document);
    }

    /**
     * Check ownership
     *
     * @param \Bpi\ApiBundle\Domain\Aggregate\Agency $agency
     * @return boolean
     */
    public function isOwner(Agency $agency)
    {
        return $this->author->getAgencyId()->equals($agency->getAgencyId());
    }

    public function getAuthor() {
      return $this->author;
    }

    public function getAgencyId()
    {
      return $this->author->getAgencyId();
    }

    public function isDeleted()
    {
      return $this->deleted;
    }

    public function setDeleted($deleted = true)
    {
      $this->deleted = $deleted;
    }

    /// Setters and getters for forms
    public function getTitle()
    {
        return $this->resource->getTitle();
    }
    public function setTitle($title)
    {
        $this->resource->setTitle($title);
    }

    public function getAudience()
    {
        return $this->audience;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getTeaser()
    {
        return $this->resource->getTeaser();
    }
    public function setTeaser($teaser)
    {
        $this->resource->setTeaser($teaser);
    }

    public function setAudience(Audience $audience)
    {
        $this->audience = $audience;
    }
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

}

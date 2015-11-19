<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Category;
use Bpi\ApiBundle\Domain\Entity\Audience;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\Entity\Tag;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;
use Bpi\RestMediaTypeBundle\XmlResponse;
use Doctrine\Common\Collections\ArrayCollection;
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
    protected $tags;
    protected $assets;

    /**
     * @param Assets $assets
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;
    }

    protected $deleted = false;

    /**
     * @var int $syndications
     */
    protected $syndications;

    public function __construct(
        Author $author,
        Resource $resource,
        Profile $profile,
        Category $category,
        Audience $audience,
        ArrayCollection $tags,
        Params $params,
        Assets $assets
    )
    {
        $this->author = $author;
        $this->resource = $resource;
        $this->profile = $profile;
        $this->params = $params;
        $this->category = $category;
        $this->audience = $audience;
        $this->tags = $tags;
        $this->assets = $assets;

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
    public function createRevision(Author $author, Resource $resource, Params $params, Assets $assets)
    {
        $builder = new \Bpi\ApiBundle\Domain\Factory\NodeBuilder;
        $node = $builder
            ->author($author)
            ->profile($this->profile)
            ->resource($resource)
            ->params($params)
            ->assets($assets)
            ->category($this->category)
            ->audience($this->audience)
            ->build();

        $node->parent = $this;
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(XmlResponse $document)
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
            (int)$this->params
                ->filter(function ($e) {
                    if ($e instanceof Editable) return true;
                })
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

        $entity->addProperty(
            $document->createProperty(
                'syndications',
                'string',
                (null === $this->getSyndications()) ? 0 : $this->getSyndications()
            )
        );

        $tags = $document->createTagsSection();
        foreach ($this->tags as $tag) {
            $serializedTag = new \Bpi\RestMediaTypeBundle\Element\Tag($tag->getTag());
            $tags->addTag($serializedTag);
        }
        $entity->setTags($tags);

        $this->profile->transform($document);
        $this->resource->transform($document);
        if (!empty($this->assets)) {
            $this->assets->transform($document);
        }
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

    /**
     * Some data like materials are dependent of syndicator context
     *
     * @param  AgencyID $syndicator
     * @return void
     */
    public function defineAgencyContext(AgencyID $syndicator)
    {
        $this->resource->defineAgencyContext($this->author->getAgencyId(), $syndicator);
    }

    public function getType() {
        return $this->resource->getType();
    }

    public function setSyndications($syndications)
    {
        $this->syndications = $syndications;
        return $this;
    }

    public function getSyndications()
    {
        return $this->syndications;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getAuthorFirstName()
    {
        return $this->author->getFirstname();
    }

    public function setAuthorFirstName($authorFirstName)
    {
        $this->author->setFirstname($authorFirstName);
        return $this;
    }

    public function getAuthorLastName()
    {
        return $this->author->getLastname();
    }

    public function setAuthorLastName($authorLastName)
    {
        $this->author->setLastname($authorLastName);
        return $this;
    }

    public function getAgencyId()
    {
        return $this->author->getAgencyId();
    }

    public function getAuthorAgencyId()
    {
        return $this->author->getAgencyId()->id();
    }

    public function setAuthorAgencyId($authorAgencyId)
    {
        $this->author->setAgencyId($authorAgencyId);
        return $this;
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

    public function getBody()
    {
        $nodeBodyObj = $this->resource->getBody();
        return $nodeBodyObj->getFlattenContent();
    }

    public function setBody($body)
    {
        $this->resource->setBody($body);
    }

    public function setAudience(Audience $audience)
    {
        $this->audience = $audience;
    }
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getAssets()
    {
        return $this->assets;
    }

    public function setCtime($ctime)
    {
        $this->ctime = $ctime;
    }

    public function getCtime()
    {
        return $this->ctime;
    }

    /**
     * @return mixed
     */
    public function getMtime()
    {
        return $this->mtime;
    }

    /**
     * @param mixed $mtime
     */
    public function setMtime($mtime)
    {
        $this->mtime = $mtime;
    }

    public function addTag($tag)
    {
        $this->tags->add($tag);
    }

    public function removeTag($tag)
    {
        $this->tags->removeElement($tag);
    }
}

<?php
namespace Bpi\ApiBundle\Domain\Entity\Profile;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;
use Bpi\ApiBundle\Domain\ValueObject\ValueObjectList;

class Taxonomy implements IPresentable
{
    /**
     * Mandatory attribute
     *
     * @var Bpi\ApiBundle\Domain\ValueObject\Audience
     */
    protected $audience;

    /**
     * Mandatory attribute
     *
     * @var Bpi\ApiBundle\Domain\ValueObject\Category
     */
    protected $category;

    /**
     * Closed optional attribute
     *
     * @var Bpi\ApiBundle\Domain\ValueObject\Yearwheel
     */
    protected $yearwheel;

    /**
     * Open optional dictionary
     *
     * @var Bpi\ApiBundle\Domain\ValueObject\ValueObjectList
     */
    protected $tags;

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Audience $audience
     * @param \Bpi\ApiBundle\Domain\ValueObject\Category $category
     */
    public function __construct(Audience $audience, Category $category)
    {
        $this->audience = $audience;
        $this->category = $category;
        $this->tags = new ValueObjectList();
    }

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Yearwheel $yearwheel
     */
    public function setYearwheel(Yearwheel $yearwheel)
    {
        $this->yearwheel = $yearwheel;
    }

    /**
     * Set tag list to the taxonomy
     *
     * @param array $tags list of tags
     */
    public function setTags(array $tags)
    {
        $this->tags = new ValueObjectList($tags);
    }

    /**
     *
     * @param \Bpi\ApiBundle\Entity\Profile\Taxonomy $taxonomy
     * @param string $field
     * @param int $order 1=asc, -1=desc
     * @return int see strcmp PHP function
     */
    public function compare(Taxonomy $taxonomy, $field, $order = 1)
    {
        if (stristr($field, '.')) {
            list($local_field, $child_field) = explode(".", $field, 2);
            return $this->$local_field->compare($taxonomy->$local_field, $child_field, $order);
        }

        $cmp = new Comparator($this->$field, $taxonomy->$field, $order);
        return $cmp->getResult();
    }

    /**
     * Called by persistence layer after object was initialized
     */
    public function wakeup()
    {
        $this->tags = new ValueObjectList();
    }

        /**
     * {@inheritdoc}
     *
     * @param \Bpi\RestMediaTypeBundle\Document $document
     */
    public function transform(Document $document)
    {
        $entity = $document->currentEntity();
        $entity->addProperty($document->createProperty(
            'category',
            'string',
            $this->category->name()
        ));
        $entity->addProperty($document->createProperty(
            'audience',
            'string',
            $this->audience->name()
        ));

        if ($this->yearwheel instanceof Yearwheel)
        {
            $entity->addProperty($document->createProperty(
                'yearwheel',
                'string',
                $this->yearwheel->name()
            ));
        }

        if ($this->tags->count())
        {
            $entity->addProperty($document->createProperty(
                'tags',
                'string',
                implode(', ', $this->tags->toArray())
            ));
        }
    }
}

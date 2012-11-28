<?php
namespace Bpi\ApiBundle\Domain\Entity\Profile;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;

class Taxonomy implements IPresentable
{
    protected $audience;
    protected $category;
//	protected $type;
//	protected $tags;

    public function __construct(Audience $audience, Category $category)
    {
        $this->audience = $audience;
        $this->category = $category;
    }

//	public function changeCategory(Category $category)
//	{
//		$this->category = $category;
//	}

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
    }
}

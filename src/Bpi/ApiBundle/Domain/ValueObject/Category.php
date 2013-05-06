<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

use Bpi\ApiBundle\Domain\Repository\CategoryRepository;
use Bpi\ApiBundle\Transform\Comparator;
use Bpi\ApiBundle\Transform\IPresentable;

class Category implements IValueObject, IPresentable
{
    /**
     *
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get name of category
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Category $category
     * @param string $field
     * @param int $order 1=asc, -1=desc
     * @return int see strcmp PHP function
     */
    public function compare(Category $category, $field, $order = 1)
    {
        $cmp = new Comparator($this->$field, $category->$field, $order);
        return $cmp->getResult();
    }

    /**
     * @param \Bpi\ApiBundle\Domain\ValueObject\Category $category
     * @return boolean
     */
    public function equals(IValueObject $category)
    {
        if (get_class($this) != get_class($category))
            return false;

        return $this->name() == $category->name();
    }

    /**
     * @param \Bpi\ApiBundle\Domain\Repository\CategoryRepository $repository
     * @return boolean
     */
    public function isInRepository(CategoryRepository $repository)
    {
        return $repository->findAll()->contains($this);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Bpi\RestMediaTypeBundle\Document $document
     */
    public function transform(\Bpi\RestMediaTypeBundle\Document $document)
    {
        $document->appendEntity(
            $entity = $document->createEntity('category')
        );

        $entity->addProperty($document->createProperty('group', 'string', 'category'));
        $entity->addProperty($document->createProperty('name', 'string', $this->name));
    }
}

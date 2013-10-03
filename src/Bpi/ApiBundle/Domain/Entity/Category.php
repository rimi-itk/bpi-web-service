<?php
namespace Bpi\ApiBundle\Domain\Entity;

class Category
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $category;

    /**
     * @param string $category
     */
    public function __construct($category = null)
    {
        $this->setCategory($category);
    }

    /**
     * Set category.
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Get category name.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get category ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}

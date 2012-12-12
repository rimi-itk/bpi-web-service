<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Bpi\ApiBundle\Domain\ValueObject\ValueObjectList;
use Doctrine\Common\Persistence\ObjectRepository;
use Bpi\ApiBundle\Domain\ValueObject\Category;

class CategoryRepository implements ObjectRepository
{
    protected $list;

    public function __construct()
    {
        $this->list = new ValueObjectList();
        $this->list->set('Other', new Category('Other'));
        $this->list->set('Event', new Category('Event'));
        $this->list->set('Music', new Category('Music'));
        $this->list->set('Facts', new Category('Facts'));
        $this->list->set('Book', new Category('Book'));
        $this->list->set('Film', new Category('Film'));
        $this->list->set('Literature', new Category('Literature'));
        $this->list->set('Themes', new Category('Themes'));
        $this->list->set('Markdays', new Category('Markdays'));
        $this->list->set('Games', new Category('Games'));
        $this->list->set('Campains', new Category('Campains'));
    }

    /**
     * {@inheritdoc}
     *
     * @return \Bpi\ApiBundle\Domain\Repository\ValueObjectList
     */
    public function findAll()
    {
        return $this->list;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id
     */
    public function find($id)
    {
        return $this->list->get($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     * @param array $orderBy
     * @param integer $limit
     * @param integer $offset
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * {@inheritdoc}
     *
     * @param array $criteria
     */
    public function findOneBy(array $criteria)
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getClassName()
    {
        return '\Bpi\ApiBundle\Domain\ValueObject\Category';
    }
}

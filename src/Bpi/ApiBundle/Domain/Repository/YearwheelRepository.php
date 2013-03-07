<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Bpi\ApiBundle\Domain\ValueObject\ValueObjectList;
use Doctrine\Common\Persistence\ObjectRepository;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;

class YearwheelRepository implements ObjectRepository
{
    protected $list;

    public function __construct()
    {
        $this->list = new ValueObjectList();
        $this->list->set('Easter', new Yearwheel('Easter'));
        $this->list->set('Christmas', new Yearwheel('Christmas'));
        $this->list->set('Summer', new Yearwheel('Summer'));
        $this->list->set('Winter', new Yearwheel('Winter'));
    }

    /**
     * Check if given yearwheel exists in repository
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Yearwheel $yearwheel
     * @return boolean
     */
    public function contains(Yearwheel $yearwheel)
    {
        return (bool)$this->list->filter(function($e) use($yearwheel) {
          if ($e instanceof Yearwheel && $e->equals($yearwheel))
              return true;
        })->count();
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
        return '\Bpi\ApiBundle\Domain\ValueObject\Yearwheel';
    }
}

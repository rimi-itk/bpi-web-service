<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Bpi\ApiBundle\Domain\ValueObject\ValueObjectList;
use Doctrine\Common\Persistence\ObjectRepository;
use Bpi\ApiBundle\Domain\ValueObject\Audience;

class AudienceRepository implements ObjectRepository
{
    protected $list;

    public function __construct()
    {
        $this->list = new ValueObjectList();
        $this->list->set('All', new Audience('All'));
        $this->list->set('Adult', new Audience('Adult'));
        $this->list->set('Kids', new Audience('Kids'));
        $this->list->set('Young', new Audience('Young'));
        $this->list->set('Elders', new Audience('Elders'));
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
        return '\Bpi\ApiBundle\Domain\ValueObject\Audience';
    }
}

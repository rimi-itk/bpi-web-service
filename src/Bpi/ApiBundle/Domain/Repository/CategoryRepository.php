<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class CategoryRepository extends DocumentRepository
{
    /**
     * Show all categories.
     *
     * @return array
     */
    public function listAll()
    {
        $qb = $this->createQueryBuilder();
        $qb->sort('category', 0);

        return $qb;
    }

    public function save($category)
    {
        $this->dm->persist($category);
        $this->dm->flush($category);
    }
}

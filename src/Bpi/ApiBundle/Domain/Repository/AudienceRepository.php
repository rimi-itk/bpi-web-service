<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class AudienceRepository extends DocumentRepository
{
    /**
     * Show all audiences.
     *
     * @return array
     */
    public function listAll()
    {
        $qb = $this->createQueryBuilder();
        $qb->sort('audience', 0);

        return $qb;
    }

    public function save($category)
    {
        $this->dm->persist($category);
        $this->dm->flush($category);
    }
}

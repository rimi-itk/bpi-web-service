<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Gedmo\Tree\Document\MongoDB\Repository\MaterializedPathRepository as DocumentRepository;
use Bpi\ApiBundle\Domain\Entity\NodeQuery;

class Node extends DocumentRepository
{
    public function findLatest()
    {
        return $this->dm->createQueryBuilder($this->getClassName())
            ->sort('ctime', 'desc')
            ->limit(20)
            ->getQuery()
            ->execute()
        ;
    }

    public function findByNodesQuery(NodeQuery $query)
    {
        return $query->executeByDoctrineQuery(
            $this->dm->createQueryBuilder($this->getClassName())
        );
    }
}

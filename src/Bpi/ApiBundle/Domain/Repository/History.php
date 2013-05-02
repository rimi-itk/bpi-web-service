<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Gedmo\Tree\Document\MongoDB\Repository\MaterializedPathRepository as DocumentRepository;
use Bpi\ApiBundle\Domain\Entity\NodeQuery;

class History extends DocumentRepository
{
    public function getByDateRangeForAgency($dateFrom, $dateTo, $agencyId)
    {
        return $this->dm->createQueryBuilder($this->getClassName())
            ->createQueryBuilder('h')
            ->select('count(u)')
            ->where('e.ctime BETWEEN :from AND :to')
   ->setParameter('start', date('Y-m-d', $dateFrom))
   ->setParameter('to', date('Y-m-d', $dateTo);
->andWhere('agency', $agencyId)
->getQuery();
$total = $query->getSingleResult();

    }

    public function findByNodesQuery(NodeQuery $query)
    {
        return $query->executeByDoctrineQuery(
            $this->dm->createQueryBuilder($this->getClassName())
        );
    }
}

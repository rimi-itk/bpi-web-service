<?php

namespace Bpi\ApiBundle\Domain\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Bpi\ApiBundle\Domain\Entity\Statistics;

/**
 * HistoryRepository.
 */
class HistoryRepository extends DocumentRepository
{
    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param array $agencyId
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Statistics
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     *
     * @deprecated
     */
    public function getStatisticsByDateRangeForAgency(\DateTime $dateFrom, \DateTime $dateTo, array $agencyId = [])
    {
        $qb = $this->createQueryBuilder()
            ->field('datetime')->gte($dateFrom)
            ->field('datetime')->lte($dateTo);

        if (!empty($agencyId)) {
            $qb->field('agency')->in($agencyId);
        }

        $qb
            ->map('function() { emit(this.action, 1); }')
            ->reduce(
                'function(k, vals) {
            var sum = 0;
            for (var i in vals) {
                sum += vals[i];
            }
            return sum;
        }');

        $result = $qb->getQuery()->execute();

        $res = [];
        foreach ($result as $r) {
            $res[$r['_id']] = $r['value'];
        }

        return new Statistics($res);
    }

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param $action
     * @param string $aggregateField
     * @param array $agencies
     *
     * @return \Doctrine\MongoDB\Iterator|\Doctrine\ODM\MongoDB\CommandCursor
     */
    public function getActivity(\DateTime $dateFrom, \DateTime $dateTo, $action, $aggregateField = 'agency', $agencies = []) {
        $ab = $this->createAggregationBuilder();
        $ab
            ->match()
                ->field('datetime')
                ->gte($dateFrom)
                ->lte($dateTo)
                ->field('action')
                ->equals($action)
            ->group()
                ->field('_id')
                ->expression('$'.$aggregateField)
                ->field('total')
                ->sum(1)
            ->sort(['total' => -1]);

        if (!empty($agencies)) {
            $ab
                ->match()
                ->field('agency')
                ->in($agencies);
        }

        return $ab->execute();
    }
}

<?php

namespace Bpi\ApiBundle\Domain\Repository;

use Bpi\ApiBundle\Domain\Aggregate\Node;
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
     * @param int $limit
     *
     * @return array
     */
    public function getActivity(\DateTime $dateFrom, \DateTime $dateTo, $action, $aggregateField = 'agency', $limit = 10) {
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
            ->sort(['total' => -1])
            ->limit($limit);

        $results = $ab->execute();

        $items = [];
        foreach ($results as $result) {
            $items[] = [
                'id' => is_string($result['_id']) ? $result['_id'] : (string) $result['_id']['$id'],
                'total' => $result['total'],
            ];
        }

        return $items;
    }

    public function getMyActivity(\DateTime $dateFrom, \DateTime $dateTo, $action, $agency) {
        $dm = $this->getDocumentManager();
        $qb = $dm
            ->createQueryBuilder(Node::class)
            ->select('_id')
            ->field('author.agency_id')
            ->equals($agency);
        $results = $qb->getQuery()->execute();

        $filterIds = [];
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $result */
        foreach ($results as $result) {
            $filterIds[] = new \MongoId($result->getId());
        }

        $ab = $dm
            ->createAggregationBuilder('BpiApiBundle:Entity\History')
            ->match()
                ->field('datetime')
                ->gte($dateFrom)
                ->lte($dateTo)
                ->field('action')
                ->equals($action)
                ->field('node.$id')
                ->in($filterIds)
            ->group()
                ->field('_id')
                ->expression('$node')
                ->field('total')
                ->sum(1)
            ->sort(['total' => -1]);

        $results = $ab->execute();

        $items = [];
        foreach ($results as $result) {
            $items[] = [
                'id' => (string) $result['_id']['$id'],
                'total' => $result['total'],
            ];
        }

        return $items;
    }
}

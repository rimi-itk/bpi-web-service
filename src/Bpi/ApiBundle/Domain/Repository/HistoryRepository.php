<?php

namespace Bpi\ApiBundle\Domain\Repository;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Aggregate\TitleWrapperInterface;
use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\StatisticsExtended;
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
     * @param $actionFilter
     * @param $aggregateField
     * @param array $agencyFilter
     * @param int $limit
     *
     * @return \Bpi\ApiBundle\Domain\Entity\StatisticsExtended
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getActivity(\DateTime $dateFrom, \DateTime $dateTo, $actionFilter, $aggregateField, $agencyFilter = [], $limit = 10) {
        $dm = $this->dm;

        $ab = $dm->createAggregationBuilder(History::class);
        $ab
            ->match()
                ->field('datetime')
                ->gte($dateFrom)
                ->lte($dateTo)
                ->field('action')
                ->equals($actionFilter);

        if ('node' == $aggregateField && !empty($agencyFilter)) {
            $qb = $dm
                ->createQueryBuilder(Node::class)
                ->field('author.agency_id')
                ->in($agencyFilter);
            $results = $qb->getQuery()->execute();

            $filterIds = [];
            /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $result */
            foreach ($results as $result) {
                $filterIds[] = new \MongoId($result->getId());
            }

            $ab
                ->match()
                ->field('node.$id')
                ->in($filterIds);
        }

        $ab
            ->group()
                ->field('_id')
                ->expression('$'.$aggregateField)
                ->field('total')
                ->sum(1)
            ->sort(['total' => -1])
            ->limit($limit);

        $results = $ab->execute();

        $activity = [];
        foreach ($results as $result) {
            $id = is_string($result['_id']) ? $result['_id'] : (string) $result['_id']['$id'];
            $activity[] = [
                'id' => $id,
                'title' => 'node' == $aggregateField ? $this->getNodeTitle($id) : $this->getAgencyTitle($id),
                'total' => $result['total'],
            ];
        }

        return new StatisticsExtended(
            $dateFrom,
            $dateTo,
            $actionFilter,
            $aggregateField,
            $activity
        );
    }

    /**
     * Gets node title.
     *
     * @param string $id
     *   Node internal id.
     *
     * @return string
     *   Node title.
     */
    private function getNodeTitle($id) {
        $dm = $this->dm;

        $entity = $dm
            ->getRepository(Node::class)
            ->find($id);

        if ($entity instanceof TitleWrapperInterface) {
            return $entity->getTitle();
        }

        return (string) $entity;
    }

    /**
     * Gets agency title.
     *
     * @param string $id
     *   Agency public id.
     *
     * @return string
     *   Agency name.
     */
    private function getAgencyTitle($id) {
        $dm = $this->dm;

        $entity = $dm
            ->getRepository(Agency::class)
            ->findByPublicId($id);

        if ($entity instanceof TitleWrapperInterface) {
            return $entity->getTitle();
        }

        return (string) $entity;
    }
}

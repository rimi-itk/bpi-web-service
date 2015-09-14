<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Bpi\ApiBundle\Domain\Entity\Statistics;

/**
 * HistoryRepository
 *
 */
class HistoryRepository extends DocumentRepository
{
  public function getStatisticsByDateRangeForAgency($dateFrom, $dateTo, $agencyId)
  {
    $dateFrom = new \DateTime($dateFrom . ' 00:00:00');
    $dateTo = new \DateTime($dateTo . ' 23:59:59');

    $qb = $this->createQueryBuilder()
        ->field('datetime')->gte($dateFrom)
        ->field('datetime')->lte($dateTo);

    if (!empty($agencyId)) {
        $qb->field('agency')->equals($agencyId);
    }

    $qb->map('function() { emit(this.action, 1); }')
        ->reduce('function(k, vals) {
            var sum = 0;
            for (var i in vals) {
                sum += vals[i];
            }
            return sum;
        }');
    $result = $qb->getQuery()->execute();

    $res = array();
    foreach ($result as $r) {
      $res[$r['_id']] = $r['value'];
    }

    return new Statistics($res);
  }

  /**
     * Gets count of syndicated.
     *
     * @param int $nid
     * @return int
     */
    public function getSyndicatedCount($nid)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('node.id')->equals($nid);
        $qb->addAnd($qb->expr()->field('action')->equals('syndicate'));
        $count = $qb->getQuery()
            ->execute()
            ->count();

        return $count;
    }
}

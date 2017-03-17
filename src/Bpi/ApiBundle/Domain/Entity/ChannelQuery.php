<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

/**
 * ChannelQuery.
 */
class ChannelQuery extends Query
{
    /**
     * {@inheritdoc}
     */
    protected $fieldMap = array(
        'name'            => 'channelName',
        'description'     => 'channelDescription',
        'nodeLastAddedAt' => 'nodeLastAddedAt',
        'agency_id'       => 'channelAdmin.userAgency.public_id',
    );

    /**
     * {@inheritdoc}
     */
    protected $searchFields = array('name', 'description');

    /**
     * {@inheritdoc}
     */
    public function executeByDoctrineQuery(QueryBuilder $query)
    {
        $query->field('channelDeleted')->equals(false);

        return parent::executeByDoctrineQuery($query);
    }
}

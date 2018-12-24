<?php

namespace Bpi\ApiBundle\Domain\Entity;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

/**
 * UserQuery
 */
class UserQuery extends Query
{
    /**
     * {@inheritdoc}
     */
    protected $fieldMap = [
        'email' => 'email',
        'firstname' => 'userFirstName',
        'lastname' => 'userLastName',
    ];

    /**
     * {@inheritdoc}
     */
    protected $searchFields = ['firstname', 'lastname', 'email'];
}

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
    protected $fieldMap = array(
        'email'     => 'email',
        'firstname' => 'userFirstName',
        'lastname'  => 'userLastName',
    );

    /**
     * {@inheritdoc}
     */
    protected $searchFields = array('firstname', 'lastname', 'email');
}

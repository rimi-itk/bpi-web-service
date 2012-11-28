<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Bpi\ApiBundle\Domain\Repository\IAudience;

class AudienceRepository implements IAudience
{
    public function findAll()
    {
        $list = new ValueObjectList();
        $list->attach(new Audience('all'));
        $list->attach(new Audience('adult'));
        return $list;
    }
}

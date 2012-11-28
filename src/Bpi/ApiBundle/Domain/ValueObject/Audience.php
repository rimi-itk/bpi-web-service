<?php
namespace Bpi\ApiBundle\Domain\ValueObject;

use Bpi\ApiBundle\Domain\Repository\AudienceRepository;
use Bpi\ApiBundle\Transform\Comparator;

class Audience implements IValueObject
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Audience $audience
     * @param string $field
     * @param int $order 1=asc, -1=desc
     * @return int see strcmp PHP function
     */
    public function compare(Audience $category, $field, $order = 1)
    {
        $cmp = new Comparator($this->$field, $category->$field, $order);
        return $cmp->getResult();
    }

    /**
     * @param \Bpi\ApiBundle\Domain\ValueObject\Audience $audience
     * @return boolean
     */
    public function equals(IValueObject $audience)
    {
        if (get_class($this) != get_class($audience))
            return false;

        return $this->name() == $audience->name();
    }

    public function isInRepository(AudienceRepository $repository)
    {
        return $repository->findAll()->contains($this);
    }
}

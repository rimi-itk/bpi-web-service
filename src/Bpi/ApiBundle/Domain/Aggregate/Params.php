<?php

namespace Bpi\ApiBundle\Domain\Aggregate;

use Closure;
use Bpi\ApiBundle\Domain\ValueObject\Param\IParam;
use Bpi\ApiBundle\Domain\ValueObject\ValueObjectList;

class Params
{
    protected $collection;

    public function __construct(array $collection = [])
    {
        $this->collection = new ValueObjectList($collection);
    }

    /**
     * Adds an element to the collection.
     *
     * @param IParam $value
     *
     * @return boolean Always TRUE.
     */
    public function add(IParam $elem)
    {
        $this->collection->add($elem);
    }

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param Closure $p The predicate used for filtering.
     *
     * @return Collection A collection with the results of the filter operation.
     */
    public function filter(Closure $p)
    {
        return $this->collection->filter($p);
    }

    /**
     * Add collection
     *
     * @param $collection
     */
    public function addCollection($collection)
    {
        $this->collection[] = $collection;
    }

    /**
     * Remove collection
     *
     * @param $collection
     */
    public function removeCollection($collection)
    {
        $this->collection->removeElement($collection);
    }

    /**
     * Get collection
     *
     * @return Doctrine\Common\Collections\Collection $collection
     */
    public function getCollection()
    {
        return $this->collection;
    }
}

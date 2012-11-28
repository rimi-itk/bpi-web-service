<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

class NodeQuery
{
    protected $filters = array();
    protected $sorts = array();
    protected $offset = 0;
    protected $amount = 20;
    protected $reduce_strategy;

    public function filter($field, $value)
    {
        $this->filters[$field] = $value;
    }

    public function offset($value)
    {
        $this->offset = $value;
    }

    public function amount($value)
    {
        $this->amount = $value;
    }

    public function sort($field, $order)
    {
        $this->sorts[$field] = strtolower($order) == 'asc' ? 1 : -1;
    }

    public function reduce($strategy)
    {
        $this->reduce_strategy = $strategy;
    }

    public function executeByDoctrineQuery(QueryBuilder $query)
    {
        foreach($this->filters as $field => $value)
            $query->field($field)->equals($value);

        $query->skip($this->offset);
        $query->limit($this->amount);

        switch ($this->reduce_strategy) {
            case 'initial':
                $query->field('level')->equals(1);
            break;
            case 'latest':
            case 'revised':
                /** @todo custom query */
            break;
        }

        $collection = $query->getQuery()->execute();

        foreach($this->sorts as $path => $order) {
            $arr_coll = $collection->toArray();
            uasort($arr_coll, function($node_a, $node_b) use ($path, $order) {
                return $node_a->compare($node_b, $path, $order);
            });
            $collection = new \Doctrine\Common\Collections\ArrayCollection($arr_coll);
        }

        return $collection;
    }
}

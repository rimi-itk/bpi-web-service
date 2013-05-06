<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

class NodeQuery
{
    public $total;

    protected $filters = array();
    protected $sorts = array();
    protected $offset = 0;
    protected $amount = 20;
    protected $reduce_strategy;
    protected $search;

    /**
     *
     * @var array
     */
    protected $field_map = array(
        'title'    => 'resource.title',
        'teaser'   => 'resource.teaser',
        'body'     => 'resource.body',
        'creation' => 'resource.creation',
        'type'     => 'resource.type',
        'ctime'    => 'ctime',
        'pushed'   => 'ctime',
        'category' => 'profile.category.name',
        'audience' => 'profile.audience.name',
        'agency_id'=> 'author.agency_id',
        'author'   => 'author.lastname',
        'firstname'=> 'author.firstname',
        'lastname' => 'author.lastname',
    );

    /**
     * Transform field names from presentation layer to persistense
     *
     * @return string
     */
    protected function map($field_name)
    {
        if (!isset($this->field_map[$field_name]))
            throw new \InvalidArgumentException(sprintf('Field "%s" has no mapping', $field_name));

        return $this->field_map[$field_name];
    }

    public function filter($field, $value)
    {
        $this->filters[$this->map($field)] = $value;
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
        $this->sorts[$this->map($field)] = strtolower($order) == 'asc' ? 1 : -1;
    }

    public function search($text)
    {
        $this->search = $text;
    }

    protected function applySearch(QueryBuilder $query)
    {
        if (!$this->search)
            return;

        $query
          ->addOr($query->expr()->field($this->map('title'))->equals($this->matchAny($this->search)))
          ->addOr($query->expr()->field($this->map('body'))->equals($this->matchAny($this->search)))
          ->addOr($query->expr()->field($this->map('teaser'))->equals($this->matchAny($this->search))
        );
    }

    protected function applyFilters(QueryBuilder $query)
    {
        foreach($this->filters as $field => $value)
            $query->field($field)->equals($this->matchAny($value));
    }

    public function reduce($strategy)
    {
        $this->reduce_strategy = $strategy;
    }

    protected function applyReduce(QueryBuilder $query)
    {
        switch ($this->reduce_strategy)
        {
            case 'initial':
                $query->field('level')->equals(1);
            break;
            case 'latest':
            case 'revised':
                /** @todo custom query */
            break;
        }
    }

    public function executeByDoctrineQuery(QueryBuilder $query)
    {
        // Hide deleted items
        $query->field('deleted')->equals(false);

        $this->applySearch($query);
        $this->applyFilters($query);
        $this->applyReduce($query);

        // Calculate total count of items before applying the limits
        $this->total = $query->getQuery()->execute()->count();

        $query->skip($this->offset);
        $query->limit($this->amount);

        $collection = $query->getQuery()->execute();

        foreach($this->sorts as $path => $order)
        {
            $arr_coll = $collection->toArray();
            uasort($arr_coll, function($node_a, $node_b) use ($path, $order) {
                return $node_a->compare($node_b, $path, $order);
            });
            $collection = new \Doctrine\Common\Collections\ArrayCollection($arr_coll);
        }

        return $collection;
    }

    protected function matchAny($value)
    {
        return new \MongoRegex('/.*' . preg_quote($value) . '.*/i');
    }
}

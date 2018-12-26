<?php

namespace Bpi\ApiBundle\Domain\Entity;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

/**
 * Query.
 */
class Query
{
    public $total;
    public $offset = 0;
    public $amount = 20;

    protected $filters = [];
    protected $sorts = [];
    protected $search;

    /**
     * @var array
     */
    protected $fieldMap = [];

    /**
     * @var array
     */
    protected $searchFields = [];

    /**
     * Set filter.
     *
     * @param array $value
     */
    public function filter($value)
    {
        if (!$value) {
            return;
        }

        $this->filters = $value;
    }

    /**
     * Set offset.
     *
     * @param array $value
     */
    public function offset($value)
    {
        $this->offset = (int)$value;
    }

    /**
     * Set amount.
     *
     * @param array $value
     */
    public function amount($value)
    {
        $this->amount = (int)$value;
    }

    /**
     * Add sorting by a field in a given direction.
     *
     * @param string $field
     * @param string $direction
     */
    public function sort($field, $direction)
    {
        $this->sorts[$this->map($field)] = strtolower($direction) == 'asc' ? 1 : -1;
    }

    /**
     * Set search.
     *
     * @param string $text
     */
    public function search($text)
    {
        $this->search = $text;
    }

    /**
     * Execute query.
     *
     * @param QueryBuilder $query
     *
     * @return mixed
     */
    public function executeByDoctrineQuery(QueryBuilder $query)
    {
        $this->applySearch($query);
        $this->applyFilters($query);
        $this->applySort($query);

        // Calculate total count of items before applying the limits
        $this->total = $query->getQuery()->execute()->count();

        $query->skip($this->offset);
        $query->limit($this->amount);

        return $query->getQuery()->execute();
    }

    /**
     * Transform field names from presentation layer to persistense
     *
     * @return string
     */
    protected function map($name)
    {
        if (!array_key_exists($name, $this->fieldMap)) {
            throw new \InvalidArgumentException(sprintf('Field "%s" has no mapping', $name));
        }

        return $this->fieldMap[$name];
    }

    protected function applySearch(QueryBuilder $query)
    {
        if (!$this->search) {
            return;
        }

        // Split search into words or keep as one term if quoted ("…")
        $terms = preg_match('/^".+"$/', $this->search)
            ? [$this->search]
            : preg_split('/\s+/', $this->search, null, PREG_SPLIT_NO_EMPTY);

        foreach ($this->searchFields as $field) {
            foreach ($terms as $term) {
                $query->addOr($query->expr()->field($this->map($field))->equals($this->matchAny($term)));
            }
        }
    }

    protected function applyFilters(QueryBuilder $query)
    {
        $query
            ->field('_id')
            ->in($this->filters);
    }

    protected function applySort(QueryBuilder $query)
    {
        foreach ($this->sorts as $path => $direction) {
            $query->sort($path, $direction);
        }
    }

    protected function matchAny($value)
    {
        return new \MongoRegex('/.*'.preg_quote($value).'.*/i');
    }
}

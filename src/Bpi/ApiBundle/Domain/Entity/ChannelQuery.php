<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

class ChannelQuery
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
        'name'         => 'channelName',
        'description'  => 'channelDescription',
        // 'body'         => 'resource.body',
        // 'creation'     => 'resource.creation',
        // 'type'         => 'resource.type',
        // 'ctime'        => 'ctime',
        'nodeLastAddedAt'       => 'nodeLastAddedAt',
        // 'category'     => 'category',
        // 'audience'     => 'audience',
        // 'assets'       => 'assets',
        'agency_id'    => 'channelAdmin.userAgency.public_id',
        // 'author'       => 'author.lastname',
        // 'firstname'    => 'author.firstname',
        // 'lastname'     => 'author.lastname',
        // 'syndications' => 'syndications'
    );

    /**
     * Transform field names from presentation layer to persistense
     *
     * @return string
     */
    protected function map($field_name)
    {
        if (!array_key_exists($field_name, $this->field_map))
            throw new \InvalidArgumentException(sprintf('Field "%s" has no mapping', $field_name));

        return $this->field_map[$field_name];
    }

    public function filter($value)
    {
        if (!$value) {
            return;
        }

        $this->filters = $value;
    }

    public function offset($value)
    {
        $this->offset = (int) $value;
    }

    public function amount($value)
    {
        $this->amount = (int) $value;
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

        $fields = array('name', 'description');
        // Split search into words or keep as one term if quoted ("…")
        $terms = preg_match('/^".+"$/', $this->search)
               ? array($this->search)
               : preg_split('/\s+/', $this->search, null, PREG_SPLIT_NO_EMPTY);

        foreach ($fields as $field) {
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

    protected function applySort(QueryBuilder $query)
    {
        foreach ($this->sorts as $path => $order) {
            $query->sort($path, $order);
        }
    }

    public function executeByDoctrineQuery(QueryBuilder $query)
    {
        // Hide deleted items
        $query->field('channelDeleted')->equals(false);

        $this->applySearch($query);
        // $this->applyFilters($query);
        // $this->applyReduce($query);
        // $this->applySort($query);

        // Calculate total count of items before applying the limits
        $this->total = $query->getQuery()->execute()->count();

        // $query->skip($this->offset);
        // $query->limit($this->amount);

        return $query->getQuery()->execute();
    }

    protected function matchAny($value)
    {
        return new \MongoRegex('/.*' . preg_quote($value) . '.*/i');
    }
}

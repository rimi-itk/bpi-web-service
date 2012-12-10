<?php
namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\NodeQuery;
use Bpi\ApiBundle\Transform\Path;

/**
 * Extract NodesQuery entity from presentation
 */
class NodesQuery implements IExtractor
{
    /**
     * @var Document
     */
    protected $doc;

    /**
     *
     * {@inheritdoc}
     */
    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    /**
     *
     * {@inheritdoc}
     * @return \Bpi\ApiBundle\Domain\Entity\NodeQuery
     */
    public function extract()
    {
        $node_query = new NodeQuery();
        $query = $this->doc->getEntity('nodes_query');
        foreach ($query->matchProperties('~^filter\[(.+)\]$~') as $match => $property) {
            $path = new Path($match);
            $node_query->filter($path->toDomain(), $property->getValue());
        }

        if ($query->hasProperty('offset', 'number'))
            $node_query->offset($query->property('offset')->getValue());

        if ($query->hasProperty('amount', 'number'))
            $node_query->amount($query->property('amount')->getValue());

        foreach ($query->matchProperties('~^sort\[(.+)]$~') as $match => $property) {
            $path = new Path($match);
            $node_query->sort($path->toDomain(), $property->getValue());
        }

        return $node_query;
    }
}

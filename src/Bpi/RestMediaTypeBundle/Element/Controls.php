<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Link;
use Bpi\RestMediaTypeBundle\Element\Scope\HasLinks;

/**
 * @Serializer\XmlRoot("hypermedia")
 */
class Controls implements HasLinks
{
    /**
     * @Serializer\XmlList(inline=true, entry="link")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Link>")
     */
    protected $links;
    
    /**
     * @Serializer\XmlList(inline=true, entry="query")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Query>")
     */
    protected $queries;

    /**
     * @Serializer\XmlList(inline=true, entry="template")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Templates>")
     */
    protected $templates;

    /**
     * 
     * @param \Bpi\RestMediaTypeBundle\Element\Element\Query $query
     * @return \Bpi\RestMediaTypeBundle\Element\Controls
     */
    public function addQuery(Query $query)
    {
        $this->queries[] = $query;
        return $this;
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Link $link
     * @return \Bpi\RestMediaTypeBundle\Element\Entity
     */
    public function addLink(Link $link)
    {
        $this->links[] = $link;
        return $this;
    }
}

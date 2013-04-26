<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;
use Bpi\RestMediaTypeBundle\Element\Link;
use Bpi\RestMediaTypeBundle\Element\Scope\HasLinks;

/**
 * @Serializer\XmlRoot("hypermedia")
 */
class Hypermedia implements HasLinks
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
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Template>")
     */
    protected $templates;

    /**
     * 
     * @param \Bpi\RestMediaTypeBundle\Element\Query $query
     * @return \Bpi\RestMediaTypeBundle\Element\Hypermedia
     */
    public function addQuery(Query $query)
    {
        $this->queries[] = $query;
        return $this;
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Link $link
     * @return \Bpi\RestMediaTypeBundle\Element\Hypermedia
     */
    public function addLink(Link $link)
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     *
     * @param \Bpi\RestMediaTypeBundle\Element\Template $template
     * @return \Bpi\RestMediaTypeBundle\Element\Hypermedia
     */
    public function addTemplate(Template $template) {
        $this->templates[] = $template;
        return $this;
    }
}

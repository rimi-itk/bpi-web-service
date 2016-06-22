<?php
namespace Bpi\RestMediaTypeBundle;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("channels")
 */
class Channels extends XmlResponse
{
    /**
     * @Serializer\XmlList(inline=true, entry="channel")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Channel>")
     */
    public $channels = array();

    /**
     * @Serializer\XmlList(inline=true, entry="facets")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\ChannelFacet>")
     */
    public $facets = array();

    public function addChannel($entity)
    {
        $entity = new \Bpi\RestMediaTypeBundle\Element\Channel($entity);
        $this->channels[] = $entity;
    }

    public function addFacet($facet)
    {
        $facet = new \Bpi\RestMediaTypeBundle\Element\ChannelFacet($facet);
        $this->facets[] = $facet;
    }

}

<?php
namespace Bpi\RestMediaTypeBundle;

use Bpi\ApiBundle\Domain\Entity\Channel;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("channels")
 */
class Channels extends Collection
{
    /**
     * @Serializer\XmlList(inline=true, entry="channel")
     */
    public $items;

    public function addChannel(Channel $channel) {
        $item = new \Bpi\RestMediaTypeBundle\Element\Channel($channel);
        return $this->addItem($item);
    }
}

// /**
//  * @Serializer\XmlRoot("channels")
//  */
// class Channels extends XmlResponse
// {
//     /**
//      * @var int
//      * @Serializer\XmlAttribute
//      */
//     public $total;

//     /**
//      * @Serializer\XmlList(inline=true, entry="channel")
//      * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\Channel>")
//      */
//     public $channels = array();

//     /**
//      * @Serializer\XmlList(inline=true, entry="facet")
//      * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\ChannelFacet>")
//      */
//     public $facets = array();

//     public function setTotal($total) {
//         $this->total = $total;

//         return $this;
//     }

//     public function addChannel($entity)
//     {
//         $entity = new \Bpi\RestMediaTypeBundle\Element\Channel($entity);
//         $this->channels[] = $entity;

//         return $this;
//     }

//     public function addFacet($facet)
//     {
//         $this->facets[] = $facet;

//         return $this;
//     }

// }

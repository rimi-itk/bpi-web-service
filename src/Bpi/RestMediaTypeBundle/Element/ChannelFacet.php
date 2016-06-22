<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("facet")
 */
class ChannelFacet
{
    /**
     * @var
     */
    private $id;

    /**
     * @var
     */
    private $channelId;

    // private $channelIds;
    // private $channelName;
    // private $channelDescription;
    // private $channelAdmin;
    // private $nodes;
    // private $nodeLastAddedAt;
    // private $users;

    /**
     * @param $data
     */
    public function __construct(\Bpi\ApiBundle\Domain\Entity\ChannelFacet $facet)
    {
        $this->id = $facet->getId();
        $this->channelId = $facet->getChannelId();
    }
}

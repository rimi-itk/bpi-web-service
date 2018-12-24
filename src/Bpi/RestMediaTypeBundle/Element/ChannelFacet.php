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
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    private $channelId;

    /**
     * @param \Bpi\ApiBundle\Domain\Entity\ChannelFacet $facet
     */
    public function __construct(\Bpi\ApiBundle\Domain\Entity\ChannelFacet $facet)
    {
        $this->id = $facet->getId();
        $this->channelId = $facet->getChannelId();
    }
}

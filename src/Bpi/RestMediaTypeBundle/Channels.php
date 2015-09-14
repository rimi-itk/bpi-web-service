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

    public function addChannel($entity)
    {
        $entity = new \Bpi\RestMediaTypeBundle\Element\Channel($entity);
        $this->channels[] = $entity;
    }

}

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

    /**
     * Add a Channel to the Collection.
     *
     * @param Channel $channel
     *
     * @return Collection
     */
    public function addChannel(Channel $channel)
    {
        $item = new \Bpi\RestMediaTypeBundle\Element\Channel($channel);

        return $this->addItem($item);
    }
}

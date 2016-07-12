<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("channel")
 */
class Channel extends Item
{
    /**
     * @var
     */
    private $id;
    /**
     * @var
     */
    private $channelName;
    /**
     * @var
     */
    private $channelDescription;
    /**
     * @var
     */
    private $channelAdmin;
    /**
     * @Serializer\XmlList(inline=false, entry="node")
     */
    private $nodes;

    /**
     * @var
     * @Serializer\Type("DateTime")
     */
    private $nodeLastAddedAt;

    /**
     * @Serializer\XmlList(inline=false, entry="user")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\User>")
     */
    private $users;

    /**
     * @param \Bpi\ApiBundle\Domain\Entity\Channel $channel
     */
    public function __construct(\Bpi\ApiBundle\Domain\Entity\Channel $channel)
    {
        $this->id = $channel->getId();
        $admin = $channel->getChannelAdmin();
        $this->channelAdmin = new User($admin);
        $this->channelName = $channel->getChannelName();
        $this->channelDescription = $channel->getChannelDescription();
        $nodes = $channel->getChannelNodes();
        foreach ($nodes as $node) {
            $this->nodes[] = $node->getId();
        }
        $this->nodeLastAddedAt = $channel->getNodeLastAddedAt();
        $users =  $channel->getChannelEditors();
        foreach ($users as $user) {
            $this->users[] = new User($user);
        }
    }
}

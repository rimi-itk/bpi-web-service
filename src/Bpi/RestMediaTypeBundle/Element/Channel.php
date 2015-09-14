<?php
namespace Bpi\RestMediaTypeBundle\Element;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("channel")
 */
class Channel
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
     * @Serializer\XmlList(inline=false, entry="user")
     * @Serializer\Type("array<Bpi\RestMediaTypeBundle\Element\User>")
     */
    private $users;

    /**
     * @param $data
     */
    public function __construct(\Bpi\ApiBundle\Domain\Entity\Channel $data)
    {
        $this->id = $data->getId();
        $admin = $data->getChannelAdmin();
        $this->channelAdmin = new User($admin);
        $this->channelName = $data->getChannelName();
        $this->channelDescription = $data->getChannelDescription();
        $nodes = $data->getChannelNodes();
        foreach($nodes as $node)
            $this->nodes[] = $node->getId();
        $users =  $data->getChannelEditors();
        foreach($users as $user)
             $this->users[] = new User($user);
    }
}

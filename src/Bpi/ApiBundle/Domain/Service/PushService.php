<?php
namespace Bpi\ApiBundle\Domain\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Knp\Bundle\GaufretteBundle\FilesystemMap;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

/**
 * Domain service for content syndication
 */
class PushService
{
    /**
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     *
     * @var Gaufrette\FilesystemMap
     */
    protected $fs_map;

    /**
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @param \Knp\Bundle\GaufretteBundle\FilesystemMap  $fs_map
     */
    public function __construct(ObjectManager $manager, FilesystemMap $fs_map)
    {
        $this->manager = $manager;
        $this->fs_map = $fs_map;
    }

    /**
     *
     * @param  \Bpi\ApiBundle\Domain\Entity\Author  $author
     * @param  Resource                             $resource
     * @param  \Bpi\ApiBundle\Domain\Entity\Profile $profile
     * @return \Bpi\ApiBundle\Domain\Aggregate\Node
     */
    public function push(Author $author, ResourceBuilder $resource_builder, Profile $profile)
    {
        $this->assignCopyleft($author, $resource_builder);
        $resource = $resource_builder->build();

        // copy assets from memory into storage
        $transaction = $resource->copyAssets($this->fs_map->get('assets'));
        if ($transaction->rollbackOnFail())
            $transaction->throwTheReason();

        $builder = new NodeBuilder;
        $node = $builder
            ->author($author)
            ->profile($profile)
            ->resource($resource)
            ->build()
        ;

        $this->manager->persist($node);
        $this->manager->flush();

        return $node;
    }

    /**
     *
     * @param  \Bpi\ApiBundle\Domain\ValueObject\NodeId $node_id
     * @param  \Bpi\ApiBundle\Domain\Entity\Author      $author
     * @param  Resource                                 $resource
     * @return \Bpi\ApiBundle\Domain\Aggregate\Node
     */
    public function pushRevision(NodeId $node_id, Author $author, Resource $resource)
    {
        $node = $this->manager->getRepository('BpiApiBundle:Aggregate\Node')->findOneById($node_id->id());

        $revision = $node->createRevision($author, $resource);

        $this->manager->persist($revision);
        $this->manager->flush();

        return $revision;
    }

    /**
     * Add agency copyleft to the resource
     *
     * @param \Bpi\ApiBundle\Domain\Entity\Author           $author
     * @param \Bpi\ApiBundle\Domain\Factory\ResourceBuilder $builder
     */
    public function assignCopyleft(Author $author, ResourceBuilder $builder)
    {
        $copyleft = $this->manager->getRepository('BpiApiBundle:Aggregate\Agency')
            ->find($author->getAgencyId()->id())
            ->getCopyleft();

        $builder->copyleft($copyleft);
    }
}

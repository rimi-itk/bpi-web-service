<?php
namespace Bpi\ApiBundle\Domain\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Knp\Bundle\GaufretteBundle\FilesystemMap;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\Entity\History;

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
     * @param  \Bpi\ApiBundle\Domain\Entity\Author    $author
     * @param  Resource                               $resource
     * @param  \Bpi\ApiBundle\Domain\Entity\Profile   $profile
     * @param  \Bpi\ApiBundle\Domain\Aggregate\Params $params
     * @return \Bpi\ApiBundle\Domain\Aggregate\Node
     */
    public function push(Author $author, ResourceBuilder $resource_builder, Profile $profile, Params $params)
    {
        $authorship = $params->filter(function($e){
            if ($e instanceof Authorship)
                return true;
        })->first();

        $this->assignCopyleft($author, $resource_builder, $authorship);
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
            ->params($params)
            ->build()
        ;

        $log = new History($node, $author->getAgencyId(), new \DateTime(), 'push');

        $this->manager->persist($node);
        $this->manager->flush();

        return $node;
    }

    /**
     *
     * @param  \Bpi\ApiBundle\Domain\ValueObject\NodeId $node_id
     * @param  \Bpi\ApiBundle\Domain\Entity\Author      $author
     * @param  ResourceBuilder                          $builder
     * @param  \Bpi\ApiBundle\Domain\Aggregate\Params   $params
     * @return \Bpi\ApiBundle\Domain\Aggregate\Node
     */
    public function pushRevision(NodeId $node_id, Author $author, ResourceBuilder $builder, Params $params)
    {
        $node = $this->manager->getRepository('BpiApiBundle:Aggregate\Node')->findOneById($node_id->id());

        $revision = $node->createRevision($author, $builder->build(), $params);

        $this->manager->persist($revision);
        $this->manager->flush();

        return $revision;
    }

    /**
     * Add agency copyleft to the resource
     *
     * @param \Bpi\ApiBundle\Domain\Entity\Author           $author
     * @param \Bpi\ApiBundle\Domain\Factory\ResourceBuilder $builder
     * @param \Bpi\ApiBundle\Domain\ValueObject\Autorship   $autorship
     */
    public function assignCopyleft(Author $author, ResourceBuilder $builder, Authorship $autorship)
    {
        $copyleft = new Copyleft;

        // Set agency as default original copyrighter
        $this->manager->getRepository('BpiApiBundle:Aggregate\Agency')
            ->find($author->getAgencyId()->id())
            ->setAuthorship($copyleft);

        if ($autorship->isPositive())
        {
            $author->setAuthorship($copyleft);
        }

        $builder->copyleft($copyleft);
    }
}

<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150814161039 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return "Count each node syndicates using information from history.";
    }

    public function up(Database $db)
    {
        $nodesQb = $db
            ->selectCollection('Node')
            ->createQueryBuilder();
        $nodes = $nodesQb
            ->getQuery()
            ->execute();

        $historyQb = $db
            ->selectCollection('History')
            ->createQueryBuilder();

        foreach ($nodes as $node) {
            $countNodeSyndications = $historyQb
                ->field('node.$id')
                ->equals(new \MongoId($node['_id']))
                ->field('action')
                ->equals('syndicate')
                ->getQuery()
                ->execute()
                ->count();

            if (0 === $countNodeSyndications) {
                continue;
            }

            $nodesQb
                ->update()
                ->field('syndications')->set($countNodeSyndications)
                ->field('_id')->equals($node['_id'])
                ->getQuery()
                ->execute();
        }
    }

    public function down(Database $db)
    {
        $db
            ->selectCollection('Node')
            ->createQueryBuilder()
            ->update()
            ->field('syndications')->unsetField()->exists(true)
            ->getQuery()
            ->execute();
    }
}

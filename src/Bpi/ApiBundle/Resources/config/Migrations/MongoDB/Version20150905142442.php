<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150905142442 extends AbstractMigration implements ContainerAwareInterface
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
        return "Removes facets, which refers to deleted nodes.";
    }

    public function up(Database $db)
    {
        $dm = $this->container->get('doctrine_mongodb')->getManager();

        $deleted = $db
            ->selectCollection('Node')
            ->createQueryBuilder()
            ->field('deleted')->equals(true)
            ->getQuery()
            ->execute()
        ;

        $facetRepo = $dm->getRepository('BpiApiBundle:Entity\Facet');

        foreach ($deleted as $node) {
            if(isset($node['_id'])) {
                $facet = $facetRepo->findOneByNodeId((string)$node['_id']);
                if ($facet) {
                    $dm->remove($facet);
                    $dm->flush();
                }
            }
        }
    }

    public function down(Database $db)
    {
    }
}

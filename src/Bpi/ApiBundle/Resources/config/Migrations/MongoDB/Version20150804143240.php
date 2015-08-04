<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150804143240 extends AbstractMigration implements ContainerAwareInterface
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
        return "Generate facets for all existing nodes.";
    }

    public function up(Database $db)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $nodeRepository = $dm->getRepository('BpiApiBundle:Aggregate\Node');

        $nodes = $nodeRepository->findAll();
        $facetRepository = $dm->getRepository('BpiApiBundle:Entity\Facet');
        foreach ($nodes as $node) {
            $facetRepository->prepareFacet($node);
        }
    }
}

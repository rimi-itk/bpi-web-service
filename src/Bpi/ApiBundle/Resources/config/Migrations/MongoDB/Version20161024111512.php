<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Bpi\ApiBundle\Domain\Entity\Facet;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161024111512 extends AbstractMigration implements ContainerAwareInterface
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
        return "Add type to all node facets";
    }

    public function up(Database $db)
    {
        $batchSize = 50;
        $i = 0;
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $facetRepo = $dm->getRepository('BpiApiBundle:Entity\Facet');
        $nodeRepository = $dm->getRepository('BpiApiBundle:Aggregate\Node');
        $facets = $facetRepo->findAll();

        foreach ($facets as $facet) {
            $facet = $facet;
            $node = $nodeRepository->find($facet->getNodeId());
            if ($node) {
                $data = $facet->getFacetData();
                $data['type'] = $node->getType();
                $facet->setFacetData($data);
                $dm->persist($facet);
            }

            if (($i % $batchSize) === 0) {
                $dm->flush();
                $dm->clear();
            }
            ++$i;
        }
        $dm->flush();
    }

    public function down(Database $db)
    {
    }
}

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
        $batchSize = 50;
        $i = 0;
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $nodeRepository = $dm->getRepository('BpiApiBundle:Aggregate\Node');

        $nodes = $nodeRepository->findAll();
        foreach ($nodes as $node) {
            $facet = new Facet();

            $agencyId = $node
                ->getAuthor()
                ->getAgencyId()
            ;

            $agency = $dm
                ->getRepository('BpiApiBundle:Aggregate\Agency')
                ->findOneBy(array('public_id' => $agencyId->id(), 'deleted' => false))
            ;

            $categoryId = $node
                ->getCategory()
                ->getId()
            ;
            $category = $dm
                ->getRepository('BpiApiBundle:Entity\Category')
                ->findOneBy(array('_id' => $categoryId))
                ->getCategory()
            ;

            $audienceId = $node
                ->getAudience()
                ->getId()
            ;
            $audience = $dm
                ->getRepository('BpiApiBundle:Entity\Audience')
                ->findOneBy(array('_id' => $audienceId))
                ->getAudience()
            ;

            $author = $node->getAuthor()->getFullName();

            $tags = array();
            $nodeTags = $node->getTags();
            foreach ($nodeTags as $key => $tag) {
                $tags[] = $tag->getTag();
            }

            if (null === $agency || null === $category || null === $audience || null === $tags) {
                continue;
            }

            $facets = array(
                'author' => array($author),
                'agency_id' => array($agencyId->id()),
                'agency_internal' => array($agency->getInternal()),
                'category' => array($category),
                'audience' => array($audience),
                'tags' => array($tags),
            );

            $setFacets = new \stdClass();
            array_walk($facets, function ($facet, $key) use (&$setFacets) {
                if (!empty($facet)) {
                    $setFacets->$key = $facet[0];
                }
            });

            $facet->setNodeId($node->getId());
            $facet->setFacetData($setFacets);

            $dm->persist($facet);

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

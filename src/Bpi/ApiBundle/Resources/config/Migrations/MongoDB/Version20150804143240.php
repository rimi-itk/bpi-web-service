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
                ->getAgencyId();

            $agency = $dm
                ->getRepository('BpiApiBundle:Aggregate\Agency')
                ->findOneBy(['public_id' => $agencyId->id(), 'deleted' => false]);

            $categoryId = $node
                ->getCategory()
                ->getId();
            $category = $dm
                ->getRepository('BpiApiBundle:Entity\Category')
                ->findOneBy(['_id' => $categoryId])
                ->getCategory();

            $audienceId = $node
                ->getAudience()
                ->getId();
            $audience = $dm
                ->getRepository('BpiApiBundle:Entity\Audience')
                ->findOneBy(['_id' => $audienceId])
                ->getAudience();

            $author = $node->getAuthor()->getFullName();

            $tags = [];
            $nodeTags = $node->getTags();
            foreach ($nodeTags as $key => $tag) {
                $tags[] = $tag->getTag();
            }

            if (null === $agency || null === $category || null === $audience || null === $tags) {
                continue;
            }

            $facets = [
                'author' => [$author],
                'agency_id' => [$agencyId->id()],
                'agency_internal' => [$agency->getInternal()],
                'category' => [$category],
                'audience' => [$audience],
                'tags' => [$tags],
            ];

            $setFacets = new \stdClass();
            array_walk(
                $facets,
                function ($facet, $key) use (&$setFacets) {
                    if (!empty($facet)) {
                        $setFacets->$key = $facet[0];
                    }
                }
            );

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

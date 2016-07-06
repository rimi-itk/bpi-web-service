<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Bpi\ApiBundle\Domain\Entity\ChannelFacet;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160623140826 extends AbstractMigration implements ContainerAwareInterface
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
        return "Generate facets for all existing channels.";
    }

    public function up(Database $db)
    {
        $batchSize = 50;
        $i = 0;
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $facetRepository = $dm->getRepository('BpiApiBundle:Entity\ChannelFacet');
        $channelRepository = $dm->getRepository('BpiApiBundle:Entity\Channel');
        $channels = $channelRepository->findAll();
        foreach ($channels as $channel) {
            $facet = $facetRepository->getFacet($channel);
            if (!$facet) {
                $facetRepository->prepareFacet($channel);
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

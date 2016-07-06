<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Bpi\ApiBundle\Domain\Entity\UserFacet;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160623133239 extends AbstractMigration implements ContainerAwareInterface
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
        return "Generate facets for all existing users.";
    }

    public function up(Database $db)
    {
        $batchSize = 50;
        $i = 0;
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $facetRepository = $dm->getRepository('BpiApiBundle:Entity\UserFacet');
        $userRepository = $dm->getRepository('BpiApiBundle:Entity\User');
        $users = $userRepository->findAll();
        foreach ($users as $user) {
            $facet = $facetRepository->getFacet($user);
            if (!$facet) {
                $facetRepository->prepareFacet($user);
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

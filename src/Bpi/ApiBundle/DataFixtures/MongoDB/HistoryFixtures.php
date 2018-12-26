<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Entity\History;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class HistoryFixtures.
 */
class HistoryFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $node = $manager
            ->getRepository(Node::class)
            ->findOneBy(
                [
                    'author.agency_id' => $this->getReference(AgencyFixtures::AGENCY_999999)->getAgencyId()->id(),
                ]
            );

        $historyEntity = new History(
            $node,
            $this->getReference(AgencyFixtures::AGENCY_999999)->getAgencyId()->id(),
            new \DateTime(),
            'test'
        );

        $manager->persist($historyEntity);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            NodeFixtures::class,
        ];
    }
}

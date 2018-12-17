<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class TagFixtures.
 */
class TagFixtures extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $tags = [
            'alpha',
            'beta',
            'gamma',
            'delta',
            'epsilon',
        ];

        foreach ($tags as $tag) {
            $tagFixture = new Tag();
            $tagFixture->setTag($tag);

            $manager->persist($tagFixture);

            $this->addReference('tag-'.$tag, $tagFixture);
        }

        $manager->flush();
    }
}

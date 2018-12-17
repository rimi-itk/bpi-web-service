<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $categories = [
            'Sport',
            'Litteratur',
        ];

        foreach ($categories as $category) {
            $categoryFixture = new Category();
            $categoryFixture->setCategory($category);

            $manager->persist($categoryFixture);

            $this->addReference('category-'.$category, $categoryFixture);
        }

        $manager->flush();
    }
}

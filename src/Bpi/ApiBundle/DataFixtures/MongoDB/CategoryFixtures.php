<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CategoryFixtures.
 */
class CategoryFixtures extends Fixture implements RandomFixtureReferenceInterface
{
    const CATEGORY_LITTERATUR = 'category-Litteratur';
    const CATEGORY_SPORT = 'category-Sport';

    /**
     * Category fixtures references.
     *
     * @var array
     */
    private static $categoryReferences;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $categories = [
            self::CATEGORY_LITTERATUR => 'Litteratur',
            self::CATEGORY_SPORT => 'Sport',
        ];

        foreach ($categories as $reference => $category) {
            $categoryFixture = new Category();
            $categoryFixture->setCategory($category);

            $manager->persist($categoryFixture);

            $this->addReference($reference, $categoryFixture);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function addReference($name, $object)
    {
        parent::addReference($name, $object);

        self::$categoryReferences[] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public static function getRandomFixtureReference()
    {
        $randomReferenceName = self::$categoryReferences[mt_rand(0, count(self::$categoryReferences) - 1)];

        return $randomReferenceName;
    }
}

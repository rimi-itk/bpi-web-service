<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Aggregate\Assets;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class NodeFixtures.
 */
class NodeFixtures extends Fixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $faker = FakerFactory::create();

        $resourceBuilder = new ResourceBuilder($this->container->get('router'));
        $resourceBuilder->title($faker->sentence);
        $resourceBuilder->body(implode("\n", $faker->paragraphs));
        $resourceBuilder->teaser($faker->paragraph);
        $resourceBuilder->type($faker->userName);
        $resourceBuilder->ctime($faker->dateTime);

        $nodeBuilder = new NodeBuilder();
        $nodeBuilder->resource($resourceBuilder->build());

        // Set audience.
        $nodeBuilder->audience($this->getReference('audience-Studerende'));
        // Set category.
        $nodeBuilder->category($this->getReference('category-Sport'));

        // Set author.
        $authorFixture = new Author(
            $this->getReference('agency-test_agency')->getAgencyId(),
            null,
            $faker->lastName,
            $faker->name
        );
        $nodeBuilder->author($authorFixture);

        // Set some tags.
        $nodeBuilder->tag(
            $this->getReference('tag-alpha')
        );
        $nodeBuilder->tag(
            $this->getReference('tag-beta')
        );

        // Set profile. (???)
        $nodeBuilder->profile(new Profile());

        // Set some parameters.
        $params = new Params();
        $params->add(
            new Authorship(true)
        );
        $params->add(
            new Editable(true)
        );
        $nodeBuilder->params($params);

        // Set assets.
        $assets = new Assets();
        $nodeBuilder->assets($assets);

        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $node */
        $node = $nodeBuilder->build();

        $manager->persist($node);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            AgencyFixtures::class,
            AudienceFixtures::class,
            CategoryFixtures::class,
            TagFixtures::class,
        ];
    }
}

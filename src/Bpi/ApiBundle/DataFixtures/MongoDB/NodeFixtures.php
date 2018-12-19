<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Aggregate\Assets;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Facet;
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
        $nodeBuilder->audience($this->getReference(AudienceFixtures::getRandomFixtureReference()));
        // Set category.
        $nodeBuilder->category($this->getReference(CategoryFixtures::getRandomFixtureReference()));

        // Set author.
        $authorFixture = new Author(
            $this->getReference(AgencyFixtures::TEST_AGENCY)->getAgencyId(),
            null,
            $faker->lastName,
            $faker->name
        );
        $nodeBuilder->author($authorFixture);

        // Set some tags.
        for($i = 0; $i < mt_rand(1, 10); $i++) {
            $nodeBuilder->tag(
                $this->getReference(TagFixtures::getRandomFixtureReference())
            );
        }

        // Set profile. (???)
        $nodeBuilder->profile(new Profile());

        // Set some parameters.
        $params = new Params();
        $params->add(
            new Authorship((boolean) mt_rand(0, 1))
        );
        $params->add(
            new Editable((boolean) mt_rand(0, 1))
        );
        $nodeBuilder->params($params);

        // Set assets.
        $assets = new Assets();
        $nodeBuilder->assets($assets);

        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $node */
        $node = $nodeBuilder->build();

        $manager->persist($node);

        /** @var \Bpi\ApiBundle\Domain\Repository\FacetRepository $facetRepository */
        $facetRepository = $manager->getRepository(Facet::class);
        $facetRepository->prepareFacet($node);

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

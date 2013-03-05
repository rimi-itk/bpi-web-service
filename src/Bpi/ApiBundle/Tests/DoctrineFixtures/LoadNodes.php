<?php

namespace Bpi\ApiBundle\Tests\DoctrineFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

class LoadNodes implements FixtureInterface
{

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function createAlphaResource()
    {
        $resource_builder = new ResourceBuilder;
        $alpha = $resource_builder
              ->body('alpha_body')
              ->teaser('alpha_teaser')
              ->title('alpha_title')
              ->ctime(new \DateTime("-1 day"))
              ->copyleft(new Copyleft('alpha_copyleft'))
              ->build()
        ;
        return $alpha;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function createBravoResource()
    {
        $resource_builder = new ResourceBuilder;
        $bravo = $resource_builder
              ->body('bravo_body')
              ->teaser('bravo_teaser')
              ->title('bravo_title')
              ->ctime(new \DateTime("+1 day"))
              ->copyleft(new Copyleft('bravo_copyleft'))
              ->build()
        ;
        return $bravo;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function createCharlieResource()
    {
        $resource_builder = new ResourceBuilder;
        $charlie = $resource_builder
              ->body('alpha_body')
              ->teaser('bravo_teaser')
              ->title('charlie_title')
              ->ctime(new \DateTime("now"))
              ->copyleft(new Copyleft('charlie_copyleft'))
              ->build()
        ;
        return $charlie;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Profile
     */
    public function createAlphaProfile()
    {
        return new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_A')));
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Profile
     */
    public function createBravoProfile()
    {
        return new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_B')));
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Profile
     */
    public function createCharlieProfile()
    {
        return new Profile(new Taxonomy(new Audience('audience_B'), new Category('category_A')));
    }

    /**
     *
     * @return Bpi\ApiBundle\Domain\Aggregate\Node
     */
    public function createAlphaNode()
    {
        $builder = new NodeBuilder();
        $node = $builder
            ->author(new Author(new AgencyId(1), 1, 'Bush', 'George'))
            ->profile($this->createAlphaProfile())
            ->resource($this->createAlphaResource())
            ->build()
        ;
        return $node;
    }

    /**
     *
     * @return Bpi\ApiBundle\Domain\Aggregate\Node
     */
    public function createBravoNode()
    {
        $builder = new NodeBuilder();
        $node = $builder
            ->author(new Author(new AgencyId(2), 1, 'Bush', 'George'))
            ->profile($this->createBravoProfile())
            ->resource($this->createBravoResource())
            ->build()
        ;
        return $node;
    }

    /**
     *
     * @return Bpi\ApiBundle\Domain\Aggregate\Node
     */
    public function createCharlieNode()
    {
        $builder = new NodeBuilder();
        $node = $builder
            ->author(new Author(new AgencyId(1), 2, 'Potter'))
            ->profile($this->createCharlieProfile())
            ->resource($this->createCharlieResource())
            ->build()
        ;
        return $node;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Aggregate\Agency
     */
    public function createAlphaAgency()
    {
        return new Agency(new AgencyId(1));
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createAlphaNode());
        $manager->persist($this->createBravoNode());
        $manager->persist($this->createCharlieNode());
        $manager->flush();
    }

}

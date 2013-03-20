<?php

namespace Bpi\ApiBundle\Tests\DoctrineFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;
use Bpi\ApiBundle\Domain\ValueObject\Tag;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\Factory\ProfileBuilder;

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
        $builder = new ProfileBuilder();
        return $builder
            ->audience(new Audience('audience_A'))
            ->category(new Category('category_A'))
            ->yearwheel(new Yearwheel('Winter'))
            ->tags('bravo, alpha, charlie')
            ->build();
        ;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Profile
     */
    public function createBravoProfile()
    {
        $builder = new ProfileBuilder();
        return $builder
            ->audience(new Audience('audience_B'))
            ->category(new Category('category_B'))
            ->yearwheel(new Yearwheel('Winter'))
            ->build();
        ;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Profile
     */
    public function createCharlieProfile()
    {
        $builder = new ProfileBuilder();
        return $builder
            ->audience(new Audience('audience_A'))
            ->category(new Category('category_B'))
            ->tags('bravo, alpha, charlie')
            ->build();
        ;
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
            ->params(new Params(array(new Editable(1), new Authorship(1))))
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
            ->params(new Params(array(new Editable(1), new Authorship(0))))
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
            ->params(new Params(array(new Editable(0), new Authorship(1))))
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
        return new Agency('Aarhus Kommunes Biblioteker', 'Agency Moderator Name', 'Publickey', 'Secret');
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

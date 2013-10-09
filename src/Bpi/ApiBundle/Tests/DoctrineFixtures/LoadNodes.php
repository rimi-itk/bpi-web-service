<?php

namespace Bpi\ApiBundle\Tests\DoctrineFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\Factory\ProfileBuilder;
use Bpi\ApiBundle\Domain\Service\PushService;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Bpi\ApiBundle\DataFixtures\MongoDB\FakeRouter;

class LoadNodes implements FixtureInterface
{
    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function createAlphaResource()
    {
        $map = $this->createFilesystemMap();
        $resource_builder = new ResourceBuilder($map->get('assets'), new FakeRouter());
        $alpha = $resource_builder
              ->body('<p>alpha_body unicode(❶)</p>')
              ->teaser('alpha_teaser unicode(❶)')
              ->title('alpha_title unicode(❶)')
              ->ctime(new \DateTime("-1 day"))
              ->copyleft(new Copyleft('alpha_copyleft unicode(❶)'))
        ;

        $alpha->addMaterial('100200:12345678');
        $alpha->addMaterial('100200:87654321');

        return $alpha;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function createBravoResource()
    {
        $map = $this->createFilesystemMap();
        $resource_builder = new ResourceBuilder($map->get('assets'), new FakeRouter());
        $bravo = $resource_builder
              ->body('<span title="bravo">bravo_body</span>')
              ->teaser('bravo_teaser')
              ->title('bravo_title')
              ->ctime(new \DateTime("+1 day"))
              ->copyleft(new Copyleft('bravo_copyleft'))
        ;
        return $bravo;
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function createCharlieResource()
    {
        $map = $this->createFilesystemMap();
        $resource_builder = new ResourceBuilder($map->get('assets'), new FakeRouter());
        $charlie = $resource_builder
              ->body('alpha_body')
              ->teaser('bravo_teaser')
              ->title('charlie_title')
              ->ctime(new \DateTime("now"))
              ->copyleft(new Copyleft('charlie_copyleft'))
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
            ->tags('foo, bar, zoo')
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
     * @return \Knp\Bundle\GaufretteBundle\FilesystemMap
     */
    protected function createFilesystemMap()
    {
        return new FilesystemMap(array('assets' => new \Gaufrette\Filesystem(new \Gaufrette\Adapter\InMemory())));
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $repo = $manager->getRepository('Bpi\ApiBundle\Domain\Aggregate\Agency');
        $agency  = $repo->findAll()->getNext();
        $service = new PushService($manager, $this->createFilesystemMap());

        // Alpha
        $service->push(
            new Author($agency->getAgencyId(), 1, 'Bush', 'George'),
            $this->createAlphaResource(),
            $this->createAlphaProfile(),
            new Params(array(new Editable(1), new Authorship(1)))
        );

        // Bravo
        $service->push(
            new Author($agency->getAgencyId(), 2, 'Potter', 'Harry'),
            $this->createBravoResource(),
            $this->createBravoProfile(),
            new Params(array(new Editable(1), new Authorship(0)))
        );

        // Charlie
        $service->push(
            new Author($agency->getAgencyId(), 2, 'Potter'),
            $this->createCharlieResource(),
            $this->createCharlieProfile(),
            new Params(array(new Editable(0), new Authorship(1)))
        );
    }
}

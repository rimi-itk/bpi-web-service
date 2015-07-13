<?php
namespace Bpi\ApiBundle\Tests\Service\Fixtures\Node;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Assets as Assets;
use Bpi\ApiBundle\Domain\Entity\File as File;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Audience;
use Bpi\ApiBundle\Domain\Entity\Category;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\Factory\ProfileBuilder;
use Bpi\ApiBundle\Domain\Service\PushService as PushService;
use Bpi\ApiBundle\DataFixtures\MongoDB\FakeRouter;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadAgencies;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;

class LoadNodes extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function createAlphaResource()
    {
        $resource_builder = new ResourceBuilder(new FakeRouter());
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
        $resource_builder = new ResourceBuilder( new FakeRouter());
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
     * @return \Bpi\ApiBundle\Domain\Aggregate\Assets
     */
     public function createAssets() {
      $data = array(
          array(
            'path' => 'http://av.storage.dev.inlead.dk/bpi/test/adhl.png',
            'name' => 'adhl',
            'extension' => 'png',
            'width' => '100',
            'height' => '100',
            'alt' => 'adhl',
            'title' => 'adhl',
            'type' => 'list_image',
          ),
          array(
            'path' => 'http://av.storage.dev.inlead.dk/bpi/test/text.xlsx',
            'name' => 'text',
            'extension' => 'xlsx',
            'width' => '100',
            'height' => '100',
            'alt' => 'text',
            'title' => 'text',
            'type' => 'attach',
          ),
        );

        $files = array();
        foreach ($data as $file) {

          $obj = new File($file);
          if($obj->createFile())
            $files[] = $obj;
        }
        return new Assets($files);
    }

    /**
     *
     * @return \Bpi\ApiBundle\Domain\Entity\Profile
     */
    public function createAlphaProfile()
    {
        $builder = new ProfileBuilder();
        return $builder
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
            ->yearwheel(new Yearwheel('Winter'))
            ->build();
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $service = new PushService($manager);

        $agency = $manager->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Agency')
        ->field('public_id')
        ->equals(LoadAgencies::AGENCY_ALPHA)
        ->getQuery()
        ->execute()
        ->getSingleResult();

        $service = new PushService($manager);
        $service->push(
            new Author($agency->getAgencyId(), 1, 'Bush', 'George'),
            $this->createAlphaResource(),
            'Event',
            'All',
            $this->createAlphaProfile(),
            new Params(array(new Editable(1), new Authorship(1))),
            $this->createAssets()
        );

    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}

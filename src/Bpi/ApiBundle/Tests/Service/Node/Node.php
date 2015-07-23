<?php
namespace Bpi\ApiBundle\Tests\Service\Node;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Guzzle\Http\Client;

use Bpi\ApiBundle\Tests\Service\BpiTest as BpiTest;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadAgencies;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadNodes;
use Bpi\ApiBundle\Domain\Entity\File as BpiFile;

use BpiTestArgs;

class Node extends BpiTest
{
   protected $domain;
   protected $guzzle;

   public function setUp()
    {
        parent::setUp();

        $this->domain = 'http://' . bpitest_domain;
        BpiFile::$base_url = $this->domain;

        $this->console = new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel);
        $this->console->setAutoExit(false);
        $this->loadFixtures = new \Symfony\Component\Console\Input\ArrayInput(array(
            "--env" => "test",
            "--quiet" => true,
            "--append" => true,
            "--fixtures" => 'src/Bpi/ApiBundle/Tests/Service/Fixtures/Node',
            'command' => 'doctrine:mongodb:fixtures:load'
        ));
        $this->console->run($this->loadFixtures);
        $this->guzzle = new Client($this->domain);

    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {

        $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Node')
            ->remove()
            ->field('author.agency_id')
            ->in(array(LoadAgencies::AGENCY_ALPHA, LoadAgencies::AGENCY_BRAVO))
            ->getQuery()
            ->execute();
        $this->em->flush();

         $agencies = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Entity\History')
            ->remove()
            ->field('agency')
            ->in(array(LoadAgencies::AGENCY_ALPHA, LoadAgencies::AGENCY_BRAVO))
            ->getQuery()
            ->execute();
        $this->em->flush();

        parent::tearDown();
    }

    public function testAssetsPostAction()
    {
        $name = (string)time();
        $params =  array
        (
            'agency_id' => '200100',
            'firstname' => 'Firstname 1',
            'lastname' => 'Lastname 1',
            'local_id' => '123321',
            'title' => $name,
            'body' => 'Nullam gravida cursus eleifend. Duis volutpat pellentesque convallis. Aenean id ligula risus. Nulla vestibulum neque ac adipiscing posuere. Proin eu faucibus augue. Proin dictum ante quam, eu iaculis dui venenatis a. Ut faucibus erat arcu, id elementum tortor vulputate eget. Aliquam sagittis sagittis dolor id gravida. Nulla facilisis nec sem ac posuere. Nunc tincidunt hendrerit posuere.<img alt="Sara Blædel" class="media-image attr__field_file_image_alt_text[und][0][value]__Sara Blædel attr__field_file_image_title_text[und][0][value]__Sara attr__alt__Sara%20Bl%C3%A6del attr__title__Sara%20Bl%C3%A6del" title="Sara Blædel" src="http://av.easyddb.dev.inlead.dk/sites/default/files/title_image/event/sara1.jpg" width="125" height="197">',
            'teaser' => 'Teaser 1',
            'creation' => '1994-11-05T08:15:30-05:00',
            'type' => 'news',
            'category' => 'Other',
            'audience' => 'All',
            'tags' => '',
            'assets[0][path]' => 'http://av.easyddb.dev.inlead.dk/sites/default/files/dams_images_display_format.png',
            'assets[0][title]' => 'Assets title 1',
            'assets[0][alt]' => 'Assets alt 1',
            'assets[0][type]' => 'list_image',
            'assets[0][name]' => 'dams_images_display_format',
            'assets[0][extension]' => 'png',
            'assets[0][height]' => '120',
            'assets[0][width]' => '80'
        );

        $headers = array('Auth' => 'BPI agency="200100", token="$1$XJZ2RVV6$FlBN25L6TIMc3B5cPG6nC1"');

        $request = $this->guzzle->post('app_dev.php/node', $headers, $params);
        $response = $request->send();
        $this->assertEquals('201', $response->getStatusCode(), "Push action failed. Status code wrong");
        if ($response->getStatusCode() == 201)
        {
            $node = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Node')
            ->field('resource.title')
            ->equals($name)
            ->getQuery()
            ->getSingleResult();

            if ($node != null)
            {
                $assets = $node->getAssets()->getCollection();
                $this->assertEquals($assets[0]->getExternal(), $params['assets[0][path]'], "External path");
                $this->assertEquals($assets[0]->getTitle(), $params['assets[0][title]'], "Title value");
                $this->assertEquals($assets[0]->getAlt(), $params['assets[0][alt]'], "Alt value");
                $this->assertEquals($assets[0]->getType(), $params['assets[0][type]'], "Type value");
                $this->assertEquals($assets[0]->getExtension(), $params['assets[0][extension]'], "Extension value");
                $this->assertEquals($assets[0]->getWidth(), $params['assets[0][width]'], "Width value");
                $this->assertEquals($assets[0]->getHeight(), $params['assets[0][height]'], "Height value");

                $this->assertEquals($assets[1]->getExternal(), "http://av.easyddb.dev.inlead.dk/sites/default/files/title_image/event/sara1.jpg", "External path");
                $this->assertEquals($assets[1]->getTitle(), "Sara Blædel", "Title value");
                $this->assertEquals($assets[1]->getAlt(), "Sara Blædel", "Alt value");
                $this->assertEquals($assets[1]->getType(), "body", "Type value");
                $this->assertEquals($assets[1]->getExtension(), "jpg", "Extension value");
                $this->assertEquals($assets[1]->getWidth(), "125", "Width value");
                $this->assertEquals($assets[1]->getHeight(), "197", "Height value");
            }
        }
    }

    public function testAssetsSyndicateAction()
    {
        $node = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Node')
            ->field('resource.title')
            ->equals("alpha_title unicode(❶)")
            ->getQuery()
            ->getSingleResult();
        if ($node != null) {
             $headers = array('Auth' => 'BPI agency="200100", token="$1$xMbAQo9U$ggK68UsjGmmcP2IGrCyld1"');
            $id = $node->getId();
            $url = "app_dev.php/node?id={$id}&type=single";
            $request = $this->guzzle->get($url, $headers);
            $response = $request->send();
            $this->assertEquals('200', $response->getStatusCode(), "Push action failed. Status code wrong");
            if ($response->getStatusCode() == 200)
            {
                $xml = $response->xml();
                $file = $xml->item->assets->file;
                if ($file != null)
                {
                    $assets = $node->getAssets()->getCollection();
                    $this->assertEquals($assets[0]->getExternal(), (string)$file['external'], "External path");
                    $this->assertEquals($assets[0]->getPath(), (string)$file['path'], "Path on Bpi");
                    $this->assertEquals($assets[0]->getTitle(), (string)$file['title'], "Title value");
                    $this->assertEquals($assets[0]->getAlt(), (string)$file['alt'], "Alt value");
                    $this->assertEquals($assets[0]->getType(), (string)$file['type'], "Type value");
                    $this->assertEquals($assets[0]->getExtension(), (string)$file['extension'], "Extension value");
                    $this->assertEquals($assets[0]->getWidth(), (string)$file['width'], "Width value");
                    $this->assertEquals($assets[0]->getHeight(), (string)$file['height'], "Height value");
                }
            }


        }
    }
}

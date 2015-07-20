<?php
namespace Bpi\ApiBundle\Tests\Service\Node;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Guzzle\Http\Client;

use Bpi\ApiBundle\Tests\Service\BpiTest as BpiTest;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadAgencies;
use Bpi\ApiBundle\Tests\Service\Fixtures\Other\LoadNodes;

use BpiTestArgs;

class Syndicated extends BpiTest
{
    protected $domain;
    protected $guzzle;

    public function setUp()
    {
        parent::setUp();

        $this->domain = 'http://' . bpitest_domain;

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

    public function testSyndicatedAction()
    {
        $node = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Aggregate\Node')
            ->field('resource.title')
            ->equals("alpha_title unicode(❶)")
            ->getQuery()
            ->getSingleResult();
        if ($node != null) {
            $id = $node->getId();

            $headers = array('Auth' => 'BPI agency="200200", token="$1$xMbAQo9U$5Qbn.36W21W4F6l6VwE.y0"');
            $url = "app_dev.php/node/syndicated?id={$id}";
            $request = $this->guzzle->get($url, $headers);
            $response = $request->send();
            $this->assertEquals('200', $response->getStatusCode(), "Syndication action failed(1). Marking node as syndicated failed.");

            $url = "app_dev.php/node?id={$id}&type=single";
            $request = $this->guzzle->get($url, $headers);
            $response = $request->send();
            $this->assertEquals('200', $response->getStatusCode(), "Syndication action failed(2). Getting xml failed.");

            if ($response->getStatusCode() == 200)
            {
                $xml = $response->xml();
                $this->assertEquals('1', (string)$xml->item->properties->xpath('//property[@name="syndicated"]')[0], "Value in xml wrong(1).");
            }

            $headers = array('Auth' => 'BPI agency="200200", token="$1$xMbAQo9U$5Qbn.36W21W4F6l6VwE.y0"');
            $url = "app_dev.php/node/syndicated?id={$id}";
            $request = $this->guzzle->get($url, $headers);
            $response = $request->send();
            $this->assertEquals('200', $response->getStatusCode(), "Syndication action failed(2). Marking node as syndicated failed.");

             $url = "app_dev.php/node?id={$id}&type=single";
            $request = $this->guzzle->get($url, $headers);
            $response = $request->send();
            $this->assertEquals('200', $response->getStatusCode(), "Syndication action failed(2). Getting xml failed.");

            if ($response->getStatusCode() == 200)
            {
                $xml = $response->xml();
                $this->assertEquals('2', (string)$xml->item->properties->xpath('//property[@name="syndicated"]')[0], "Value in xml wrong(2).");
            }
        }
    }
}

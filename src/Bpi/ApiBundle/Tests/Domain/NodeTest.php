<?php
namespace Bpi\ApiBundle\Tests\Domain;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Entity\Category;
use Bpi\ApiBundle\Domain\Entity\Audience;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

class NodeTest extends \PHPUnit_Framework_TestCase
{
    protected $resources;
    protected $nodes;
    protected $authors;

    public function setUp()
    {
        /** @todo merge this into common domain fixtures */
        $this->nodes = new \stdClass();
        $this->resources = new \stdClass();
        $this->authors = new \stdClass();

        $util = new Util();
        $resource_builder = $util->createResourceBuilder();
        $this->resources->alpha = $resource_builder
            ->body('alpha_body')
            ->teaser('alpha_teaser')
            ->title('alpha_title')
            ->ctime(new \DateTime("-1 day"))
            ->copyleft(new Copyleft('copyleft'))
            ->build()
        ;

        $this->resources->bravo = $resource_builder
            ->body('bravo_body')
            ->teaser('bravo_teaser')
            ->title('bravo_title')
            ->ctime(new \DateTime("+1 day"))
            ->build()
        ;

        $this->authors->alpha = new Author(new AgencyId(mt_rand()), mt_rand(), 'alpha_author');

        $profile_alpha = new Profile();
        $profile_bravo = new Profile();

        $builder = new NodeBuilder();

        $this->nodes->alpha = $builder
            ->author($this->authors->alpha)
            ->profile($profile_alpha)
            ->resource($this->resources->alpha)
            ->params(new Params(array('autorship' => new Authorship(1), 'editable' => new Editable(1))))
            ->category(new Category('Other'))
            ->audience(new Audience('All'))
            ->build();

        $this->nodes->bravo = $builder
            ->author($this->authors->alpha)
            ->profile($profile_bravo)
            ->resource($this->resources->bravo)
            ->params(new Params(array('autorship' => new Authorship(0), 'editable' => new Editable(1))))
            ->category(new Category('Other'))
            ->audience(new Audience('All'))
            ->build();
    }

    public function testCompare()
    {
        $this->assertEquals(-1, $this->nodes->alpha->compare($this->nodes->bravo, 'resource.title', 1));
        $this->assertEquals(1, $this->nodes->alpha->compare($this->nodes->bravo, 'resource.title', -1));
    }

    public function testRevisions()
    {
        $revision = $this->nodes->alpha->createRevision(
            $this->authors->alpha,
            $this->resources->bravo,
            new Params(array('autorship' => new Authorship(0), 'editable' => new Editable(0)))
        );
        $this->assertInstanceOf('\Bpi\ApiBundle\Domain\Aggregate\Node', $revision);
    }
}

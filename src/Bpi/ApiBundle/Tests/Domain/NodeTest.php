<?php
namespace Bpi\ApiBundle\Tests\Domain;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

class NodeTest extends \PHPUnit_Framework_TestCase
{
	protected $resources;
	protected $nodes;
	protected $authors;

	public function setUp()
	{
		//TODO: merge this into common domain fixtures
		$this->nodes = new \stdClass();
		$this->resources = new \stdClass();
		$this->authors = new \stdClass();
		
		$resource_builder = new ResourceBuilder;
		$this->resources->alpha = $resource_builder
			->body('alpha_body')
			->teaser('alpha_teaser')
			->title('alpha_title')
			->ctime(new \DateTime("-1 day"))
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
		
		$profile_alpha = new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_A')));
		$profile_bravo = new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_B')));
		
		$builder = new NodeBuilder();
		
		$this->nodes->alpha = $builder
			->author($this->authors->alpha)
			->profile($profile_alpha)
			->resource($this->resources->alpha)
			->build();
		
		$this->nodes->bravo = $builder
			->author($this->authors->alpha)
			->profile($profile_bravo)
			->resource($this->resources->bravo)
			->build();
	}
	
	public function testCompare()
	{
		$this->assertEquals(-1, $this->nodes->alpha->compare($this->nodes->bravo, 'resource.title', 1));
		$this->assertEquals(1, $this->nodes->alpha->compare($this->nodes->bravo, 'resource.title', -1));
		
		$this->assertEquals(-1, $this->nodes->alpha->compare($this->nodes->bravo, 'profile.taxonomy.category.name', 1));
		$this->assertEquals(1, $this->nodes->alpha->compare($this->nodes->bravo, 'profile.taxonomy.category.name', -1));
		
		$this->assertEquals(0, $this->nodes->alpha->compare($this->nodes->bravo, 'profile.taxonomy.audience.name', 1));
	}
	
	public function testRevisions()
	{
		$this->assertInstanceOf('\Bpi\ApiBundle\Domain\Aggregate\Node', $this->nodes->alpha->createRevision($this->authors->alpha, $this->resources->bravo));
	}
}
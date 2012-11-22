<?php
namespace Bpi\ApiBundle\Tests\Domain;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

class NodeTest extends \PHPUnit_Framework_TestCase
{
	protected $nodes;
	
	public function setUp()
	{
		//TODO: merge this into common domain fixtures
		$this->nodes = new \stdClass();
		
		$resource_builder = new ResourceBuilder;
		$alpha = $resource_builder
			->userId(1)
			->body('alpha_body')
			->teaser('alpha_teaser')
			->title('alpha_title')
			->ctime(new \DateTime("-1 day"))
			->build()
		;
		
		$bravo = $resource_builder
			->userId(2)
			->body('bravo_body')
			->teaser('bravo_teaser')
			->title('bravo_title')
			->ctime(new \DateTime("+1 day"))
			->build()
		;
		
		$profile_alpha = new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_A')));
		$profile_bravo = new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_B')));
		
		$builder = new NodeBuilder();
		
		$this->nodes->alpha = $builder
			->agency(new Agency(new AgencyId(1)))
			->profile($profile_alpha)
			->resource($alpha)
			->build();
		
		$this->nodes->bravo = $builder
			->agency(new Agency(new AgencyId(1)))
			->profile($profile_bravo)
			->resource($bravo)
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
}
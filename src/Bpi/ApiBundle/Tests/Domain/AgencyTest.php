<?php
namespace Bpi\ApiBundle\Tests\Domain;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy as ProfileTaxonomy;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;

class AgencyTest extends \PHPUnit_Framework_TestCase
{
	protected function generateResourceInstance()
	{
		$builder = new ResourceBuilder();
		return $builder->body('body')
			->title('title')
			->teaser('teaser')
			->userId(mt_rand())
			->ctime(new \DateTime)
			->build();
	}
	
	public function testPushResource()
	{
		$agency = new Agency(new AgencyId(mt_rand()));
		$resource = $this->generateResourceInstance();
		$profile = new Profile(new ProfileTaxonomy(new Audience('foo'), new Category('zoo')));
		
		$node = $agency->push($resource, $profile);
		
		$this->assertInstanceOf('Bpi\ApiBundle\Domain\Aggregate\Node', $node);
	}
}
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
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

class LoadNodes implements FixtureInterface
{

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		$resource_builder = new ResourceBuilder;
		$alpha = $resource_builder
			->body('alpha_body')
			->teaser('alpha_teaser')
			->title('alpha_title')
			->ctime(new \DateTime("-1 day"))
			->build()
		;
		
		$bravo = $resource_builder
			->body('bravo_body')
			->teaser('bravo_teaser')
			->title('bravo_title')
			->ctime(new \DateTime("+1 day"))
			->build()
		;
		
		$charlie = $resource_builder
			->body('alpha_body')
			->teaser('bravo_teaser')
			->title('charlie_title')
			->ctime(new \DateTime("now"))
			->build()
		;
		
		$profile_alpha = new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_A')));
		$profile_bravo = new Profile(new Taxonomy(new Audience('audience_A'), new Category('category_B')));
		$profile_charlie = new Profile(new Taxonomy(new Audience('audience_B'), new Category('category_A')));
		
		$builder = new NodeBuilder();
		$manager->persist($builder
			->author(new Author(new AgencyId(1), 1, 'Bush', 'George'))
			->profile($profile_alpha)
			->resource($alpha)
			->build()
		);
		$manager->persist($builder
			->author(new Author(new AgencyId(2), 1, 'Bush', 'George'))
			->profile($profile_bravo)
			->resource($bravo)
			->build()
		);
		$manager->persist($builder
			->author(new Author(new AgencyId(1), 2, 'Potter'))
			->profile($profile_charlie)
			->resource($charlie)
			->build()
		);
		
		$manager->flush();
	}
}
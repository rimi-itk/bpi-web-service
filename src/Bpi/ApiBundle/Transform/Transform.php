<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\ApiBundle\Rest\Resource;
use Bpi\ApiBundle\Domain\Factory\NodeBuilder;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\NodeQuery;

class Transform
{
	public function __construct()
	{
		
	}
	
	public function presentationToDomain(Resource $resource)
	{
		$result = array();
		foreach ($resource->getEntities() as $entity)
		{
			switch ($entity->getName())
			{
				case 'resource': 
					$result['entities']['resource'] = $this->transformResourceEntity($entity);
				break;
				case 'agency': 
					$this->transformAgencyEntity($entity);
				break;
				case 'profile': 
					$this->transformProfileEntity($entity);
				break;
			}
		}
	}
	
	public function presentationToNodesQuery(Document $document)
	{
		$node_query = new NodeQuery();
		$query = $document->getEntity('nodes_query');
		foreach ($query->matchProperties('~^filter\[(.+)\]$~') as $match => $property)
		{
			$path = new Path($match);
			$node_query->filter($path->toDomain(), $property->getValue());
		}
		
		if ($query->hasProperty('offset', 'number'))
			$node_query->offset($query->property('offset')->getValue());
		
		if ($query->hasProperty('amount', 'number'))
			$node_query->amount($query->property('amount')->getValue());
		
		foreach ($query->matchProperties('~^sort\[(.+)]$~') as $match => $property)
		{
			$path = new Path($match);
			$node_query->sort($path->toDomain(), $property->getValue());
		}
		
		return $node_query;
	}
	
	public function presentationToPushCommand(Document $document)
	{
		 $agency = $this->transformAgencyEntity($document->getEntity('agency'));
		 $profile = $this->transformProfileEntity($document->getEntity('profile'));
		 $resource = $this->transformResourceEntity($document->getEntity('resource'));
		 return new \Bpi\ApiBundle\Domain\Command\PushCommand($agency, $profile, $resource);
	}
	
	/**
	 * @return \Bpi\ApiBundle\Rest\Resource
	 */
	public function domainToRepresentation(IPresentable $model)
	{
		$document = new Document();
		$model->transform($document);
		return $document;
	}
	
	/**
	 * @return \Bpi\ApiBundle\Rest\Resource
	 */
	public function transformMany($models)
	{
		$document = new Document();
		foreach ($models as $model)
			$model->transform($document);
		return $document;
	}
	
	protected function transformAgencyEntity($entity)
	{
		return new Agency(new AgencyId($entity->property('agency_id')->getValue()));
	}
	
	protected function transformProfileEntity($entity)
	{
		return new Profile(new \Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy(new \Bpi\ApiBundle\Domain\ValueObject\Audience($entity->property('audience')->getValue()), new \Bpi\ApiBundle\Domain\ValueObject\Category($entity->property('category')->getValue())));
	}
	
	protected function transformResourceEntity($entity)
	{
		$builder = new ResourceBuilder();
		return $builder
			->title($entity->property('body')->getValue())
			->body($entity->property('title')->getValue())
			->userId($entity->property('user_id')->getValue())
			->teaser($entity->property('teaser')->getValue())
			->ctime(new \DateTime($entity->property('ctime')->getValue())) //TODO: transform that
			->build()
		;
	}
	
	protected function transformNodeEntity($entity)
	{
		$builder = new NodeBuilder();
		$builder->agency(new Agency(new AgencyId($entity->property('agency_id')->getValue())));
		// etc
	}
}
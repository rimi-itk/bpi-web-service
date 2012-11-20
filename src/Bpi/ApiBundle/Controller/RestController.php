<?php

namespace Bpi\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc; // @ApiDoc(resource=true, description="Filter",filters={{"name"="a-filter", "dataType"="string", "pattern"="(foo|bar) ASC|DESC"}})
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Bpi\ApiBundle\Rest\Factory\ResourceFactory;
use Bpi\ApiBundle\Rest\Factory\LinkFactory;
use Bpi\ApiBundle\Rest\VersionResolver;
use Bpi\ApiBundle\Rest\Entity\Node;
use Bpi\RestMediaTypeBundle\Document;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Component\HttpFoundation\Response;

class RestController extends FOSRestController
{
	/**
	 * @Rest\Get("/node/list")
	 */
	public function listAction()
	{
		$dm = $this->get('doctrine.odm.mongodb.document_manager');
		$node_collection = $dm->getRepository('BpiApiBundle:Aggregate\Node')->findAll();

		$tr = new \Bpi\ApiBundle\Transform\Transform;
		$document = $tr->transformMany($node_collection);
//		$document->getEntity('node')
//			->addLink($document->createLink('self', "asd"))
//			->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)))
//		;
		
		if ($this->getRequest()->get('_format') == 'html')
		{
			$doc2 = new \Bpi\RestMediaTypeBundle\Document();
			$document = array('data' => array('output' => $document, 'input' => array('example' => $doc2, 'expected_entities' => array('nodes_query'))));
		}		
		
//		$params = $this->container->get("fos_rest.request.param_fetcher");
//		print_r($params->get("sort", true));
//		print_r($params->get("filter", true));
		$view = $this->view($document, 200)
			->setTemplate("BpiApiBundle:Rest:list.html.twig")
		;
		
		return $this->handleView($view);
	}
	
	public function postNodeListAction()
	{
		$serializer = $this->get("serializer");
		$dm = $this->get('doctrine.odm.mongodb.document_manager');
		$document = $serializer->deserialize($this->getRequest()->getContent(), 'Bpi\RestMediaTypeBundle\Document', 'xml');
		$tr = new \Bpi\ApiBundle\Transform\Transform;
		$node_collection = $tr->presentationToNodesQuery($document, $dm->createQueryBuilder('BpiApiBundle:Aggregate\Node'));
		
//		if (false === $query = @simplexml_load_string($this->getRequest()->getContent()))
//		{
//			//TODO: link to NodesQuery entity schema
//			throw new HttpException(400, "Expected 'NodesQuery' BPI entity");
//		}
		
		$document = $tr->transformMany($node_collection);
		
		if ($this->getRequest()->get('_format') == 'html')
		{
			$doc2 = new \Bpi\RestMediaTypeBundle\Document();
			$document = array('data' => array('output' => $document, 'input' => array('example' => $doc2, 'expected_entities' => array('nodes_query'))));
		}
		
		$view = $this->view($document, 200)
			->setTemplate("BpiApiBundle:Rest:list.html.twig")
		;
		
		return $this->handleView($view);
	}
	
	/**
	 * @Rest\Get("/node/item/{id}")
	 */
	public function nodeAction($id)
	{
		$dm = $this->get('doctrine.odm.mongodb.document_manager');
		$_node = $dm->getRepository('BpiApiBundle:Aggregate\Node')->findOneById($id);
		
		$tr = new \Bpi\ApiBundle\Transform\Transform;
		$document = $tr->domainToRepresentation($_node);
		$document->getEntity('node')
			->addLink($document->createLink('self', "asd"))
			->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)))
		;
		
		$view = $this->view($document, 200)
			->setTemplate("BpiApiBundle:Default:index.html.twig");

		return $this->handleView($view);
	}
	
	/**
	 * @Rest\Post("/node")
	 * @Rest\View(statusCode=201)
	 * Rest\RequestParam(name="test", requirements=".+", description="Firstname")
	 * @ApiDoc()
	 */
	public function createNodeAction()
	{
		$serializer = $this->get("serializer");
		$request = $this->get("request");
		$dm = $this->get('doctrine.odm.mongodb.document_manager');
//		$params = $this->container->get("fos_rest.request.param_fetcher");
		
		try
		{
			$document = $serializer->deserialize($request->getContent(), 'Bpi\RestMediaTypeBundle\Document', 'xml');

			$transformer = new \Bpi\ApiBundle\Transform\Transform;
			$push_command = $transformer->presentationToPushCommand($document);
			
			$node_model = $push_command->execute();

			$dm->persist($node_model);
			$dm->flush();

			$node_presentation = $transformer->domainToRepresentation($node_model);
			
			$view = $this->view($node_presentation, 201)
				->setTemplate("BpiApiBundle:Default:index.html.twig");

			return $this->handleView($view);
		}
		catch(\Exception $e)
		{
			echo $e;
		}
	}
	
	/**
	 * @Rest\Get("/schema/{tree}/{subtree}")
	 */
	public function schemaAction($tree, $subtree)
	{
		if ($tree == 'entity')
		{
			if ($subtree == 'nodes_query')
			{
				if ($this->getRequest()->get('_format') == 'rng')
					throw new \Exception('Not implemented');
					
				$doc = new Document();
				$entity = $doc->createEntity('nodes_query');
				$entity->addProperty($doc->createProperty('filter[field_name]', 'string', 'value'));
				$entity->addProperty($doc->createProperty('sort[field_name]', 'string', 'value'));
				$entity->addProperty($doc->createProperty('offset', 'number', '0'));
				$entity->addProperty($doc->createProperty('amount', 'number', '10'));
				$entity->addProperty($doc->createProperty('reduce', 'string', 'latest/initial'));
				$doc->appendEntity($entity);
				$view = $this->view($doc, 200);
				return $this->handleView($view);
			}
		}
		
		throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
	}
	
	/**
	 * For testing purposes. Simply echoes back sent request
	 * 
	 * @Rest\Get("/tools/echo")
	 * @ApiDoc()
	 */
	public function echoAction()
	{
		$view = $this->view($this->get('request')->getContent(), 200);		
		return $this->handleView($view);
	}
}
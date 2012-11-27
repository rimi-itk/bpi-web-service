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
	protected function getRepository($name)
	{
		$dm = $this->get('doctrine.odm.mongodb.document_manager');
		return $dm->getRepository($name);
	}

	/**
	 * @Rest\Get("/node/list")
	 */
	public function listAction()
	{
		$node_collection = $this->getRepository('BpiApiBundle:Aggregate\Node')->findLatest();

		$tr = new \Bpi\ApiBundle\Transform\Transform;
		$document = $tr->transformMany($node_collection);
		$document->walkEntities(function($e) use ($document) {
			$e->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $e->property('id')->getValue()), true)));
			$e->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));
		});
		
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
	
	public function postNodeListAction()
	{
		$serializer = $this->get("serializer");
		$document = $serializer->deserialize($this->getRequest()->getContent(), 'Bpi\RestMediaTypeBundle\Document', 'xml');
		$tr = new \Bpi\ApiBundle\Transform\Transform;
		$node_collection = $this->getRepository('BpiApiBundle:Aggregate\Node')
			->findByNodesQuery($tr->presentationToNodesQuery($document));
		
//		if (false === $query = @simplexml_load_string($this->getRequest()->getContent()))
//		{
//			//TODO: link to NodesQuery entity schema
//			throw new HttpException(400, "Expected 'NodesQuery' BPI entity");
//		}
		
		$document = $tr->transformMany($node_collection);
		$document->walkEntities(function($e) use ($document) {
			$e->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $e->property('id')->getValue()), true)));
			$e->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));
		});
		
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
		$_node = $this->getRepository('BpiApiBundle:Aggregate\Node')->findOneById($id);
		
		$tr = new \Bpi\ApiBundle\Transform\Transform;
		$document = $tr->domainToRepresentation($_node);
		$node = $document->getEntity('node');
		$node->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $node->property('id')->getValue()), true)));
		$node->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));
		
		$view = $this->view($document, 200)
			->setTemplate("BpiApiBundle:Rest:item.html.twig");

		return $this->handleView($view);
	}
	
	/**
	 * @Rest\Post("/node/item/{id}")
	 */
	public function postModifiedNodeAction($id)
	{
		$node = $this->getRepository('BpiApiBundle:Aggregate\Node')->findOneById($this->getRequest()->get('id'));
		$document = $this->get("serializer")->deserialize($this->getRequest()->getContent(), 'Bpi\RestMediaTypeBundle\Document', 'xml');
		
		$tr = new \Bpi\ApiBundle\Transform\Transform;
		$command = $tr->presentationToPushRevisionCommand($document);
		$command->setParent($node);
		$revision = $command->execute();
		
		$dm = $this->get('doctrine.odm.mongodb.document_manager');
		$dm->persist($revision);
		$dm->flush();
		
		$view = $this->view($tr->domainToRepresentation($revision), 201)
			->setTemplate("BpiApiBundle:Rest:item.html.twig");

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
				->setTemplate("BpiApiBundle:Rest:push.html.twig");

			return $this->handleView($view);
		}
		catch(\Exception $e)
		{
			echo $e;
		}
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
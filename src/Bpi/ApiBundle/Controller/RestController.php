<?php

namespace Bpi\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc; // @ApiDoc(resource=true, description="Filter",filters={{"name"="a-filter", "dataType"="string", "pattern"="(foo|bar) ASC|DESC"}})
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Presentation;

/**
 * Main entry point for REST requests
 */
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

		$document = Presentation::transformMany($node_collection);
		$document->walkEntities(function($e) use ($document) {
			$e->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $e->property('id')->getValue()), true)));
			$e->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));
		});
		
		if ($this->getRequest()->get('_format') == 'html')
		{
			$doc2 = new Document();
			$document = array('data' => array('output' => $document, 'input' => array('example' => $doc2, 'expected_entities' => array('nodes_query'))));
		}		
		
		$view = $this->view($document, 200)
			->setTemplate("BpiApiBundle:Rest:list.html.twig")
		;
		
		return $this->handleView($view);
	}
	
	public function postNodeListAction()
	{
		$document = $this->get("serializer")->deserialize($this->getRequest()->getContent(), 'Bpi\RestMediaTypeBundle\Document', 'xml');
		$extractor = new \Bpi\ApiBundle\Transform\Extractor($document);
		$node_collection = $this->getRepository('BpiApiBundle:Aggregate\Node')
			->findByNodesQuery($extractor->extract('nodesQuery'));
		
		$document = Presentation::transformMany($node_collection);
		$document->walkEntities(function($e) use ($document) {
			$e->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $e->property('id')->getValue()), true)));
			$e->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));
		});
		
		if ($this->getRequest()->get('_format') == 'html')
		{
			$doc2 = new Document();
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
		
		$document = Presentation::transform($_node);
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
	public function postNodeRevisionAction($id)
	{
		$document = $this->get("serializer")->deserialize($this->getRequest()->getContent(), 'Bpi\RestMediaTypeBundle\Document', 'xml');
		
		$extractor = new \Bpi\ApiBundle\Transform\Extractor($document);

		$revision = $this->get('domain.push_service')->pushRevision(
			new \Bpi\ApiBundle\Domain\ValueObject\NodeId($id), 
			$extractor->extract('agency.author'), 
			$extractor->extract('resource')
		);
		
		$view = $this->view(\Bpi\ApiBundle\Transform\Presentation::transform($revision), 201)
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
		$document = $this->get("serializer")->deserialize($this->getRequest()->getContent(), 'Bpi\RestMediaTypeBundle\Document', 'xml');

		$extractor = new \Bpi\ApiBundle\Transform\Extractor($document);

		$node = $this->get('domain.push_service')->push(
			$extractor->extract('agency.author'), 
			$extractor->extract('resource'), 
			$extractor->extract('profile')
		);

		$view = $this->view(\Bpi\ApiBundle\Transform\Presentation::transform($node), 201)
			->setTemplate("BpiApiBundle:Rest:push.html.twig");

		return $this->handleView($view);
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
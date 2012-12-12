<?php

namespace Bpi\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Extractor;

/**
 * Main entry point for REST requests
 */
class RestController extends FOSRestController
{
    /**
     * Get entity repository
     *
     * @param string $name repository name
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getRepository($name)
    {
        return $this->get('doctrine.odm.mongodb.document_manager')->getRepository($name);
    }

    /**
     * Get unserialized request body
     *
     * @return Bpi\RestMediaTypeBundle\Document
     */
    protected function getDocument()
    {
        $request_body = $this->getRequest()->getContent();

        /**
         * @todo validate against schema (logical check)
         */
        if (empty($request_body) || false === simplexml_load_string($request_body))
            throw new HttpException(400, 'Bad Request'); // syntax check fail

        $document = $this->get("serializer")->deserialize(
              $request_body,
              'Bpi\RestMediaTypeBundle\Document',
              'xml'
        );
        $document->setRouter($this->get('router'));
        return $document;
    }

    /**
     * Main page of API redirects to human representation of entry point
     *
     * @Rest\Get("/")
     */
    public function indexAction()
    {
        return $this->redirectView($this->get('router')->generate('list').'.html', 302);
    }

     /**
     * Default node listing
     *
     * @Rest\Get("/node/list")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="200")
     */
    public function listAction()
    {
        $node_collection = $this->getRepository('BpiApiBundle:Aggregate\Node')->findLatest();

        $document = $this->get("bpi.presentation.transformer")->transformMany($node_collection);
        $router = $this->get('router');
        $document->walkEntities(function($e) use ($document, $router) {
            $e->addLink($document->createLink('self', $router->generate('node', array('id' => $e->property('id')->getValue()), true)));
            $e->addLink($document->createLink('collection', $router->generate('list', array(), true)));
            $e->addLink($document->createLink('assets', $router->generate('put_node_asset', array('node_id' => $e->property('id')->getValue(), 'filename' => ''), true)));
        });

        return $document;
    }

    /**
     * List nodes by recieved nodes_query
     *
     * @Rest\Post("/node/list")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig")
     */
    public function postNodeListAction()
    {
        $extractor = new Extractor($this->getDocument());
        $node_collection = $this->getRepository('BpiApiBundle:Aggregate\Node')
            ->findByNodesQuery($extractor->extract('nodesQuery'));

        $document = $this->get("bpi.presentation.transformer")->transformMany($node_collection);
        $router = $this->get('router');
        $document->walkEntities(function($e) use ($document, $router) {
            $e->addLink($document->createLink('self', $router->generate('node', array('id' => $e->property('id')->getValue()), true)));
            $e->addLink($document->createLink('collection', $router->generate('list', array(), true)));
            $e->addLink($document->createLink('assets', $router->generate('put_node_asset', array('node_id' => $e->property('id')->getValue(), "filename" => ""), true)));
        });

        return $document;
    }

     /**
      * Display available options
      * 1. HTTP verbs
      * 2. Expected media type entities in input/output
      *
      * @Rest\Options("/node/list")
      */
    public function nodeListOptionsAction()
    {
        $options = array(
              'GET' => array(
                    'output' => array(
                          'entities' => array(
                                'node'
                          )
                    )
              ),
              'OPTIONS' => array(),
              'POST' => array(
                    'input' => array(
                          'entities' => array(
                                'nodes_query'
                          )
                    ),
                    'output' => array(
                          'entities' => array(
                                'node'
                          )
                    )
              )
        );
        $headers = array('Allow' => implode(', ', array_keys($options)));
        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * List available media type entities
     *
     * @category test interface
     * @Rest\Get("/shema/entity/list")
     * @Rest\View
     */
    public function schemaListEntitiesAction()
    {
        $response = array('list' => array('nodes_query', 'node', 'agency', 'profile', 'resource'));
        sort($response['list']);
        return $response;
    }

    /**
     * Display example of entity
     *
     * @category test interface
     * @Rest\Get("/shema/entity/{name}")
     * @Rest\View()
     */
    public function schemaEntityAction($name)
    {
        $loader = new \Bpi\ApiBundle\Tests\DoctrineFixtures\LoadNodes();
        $transformer = $this->get("bpi.presentation.transformer");
        switch ($name) {
            case 'node':
                return $transformer->transform($loader->createAlphaNode());
            break;
            case 'resource':
                return $transformer->transform($loader->createAlphaResource());
            break;
            case 'profile':
                return $transformer->transform($loader->createAlphaProfile());
            break;
            case 'agency':
                return $transformer->transform($loader->createAlphaAgency());
            break;
            case 'nodes_query':
                $doc = new Document;
                $doc->appendEntity($entity = $doc->createEntity('nodes_query'));
                $entity->addProperty($doc->createProperty('amount', 'number', 10));
                $entity->addProperty($doc->createProperty('offset', 'number', 0));
                $entity->addProperty($doc->createProperty('filter[resource.title]', 'string', ''));
                $entity->addProperty($doc->createProperty('sort[ctime]', 'string', 'desc'));
                $entity->addProperty($doc->createProperty('reduce', 'string', 'initial', 'Reduce revisions to initial or latest'));
                return $doc;
            default:
                throw new HttpException(404, 'Requested entity does not exists');
        }
    }

    /**
     * Display node item
     *
     * @Rest\Get("/node/item/{id}")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig")
     */
    public function nodeAction($id)
    {
        $_node = $this->getRepository('BpiApiBundle:Aggregate\Node')->findOneById($id);

        $document = $this->get("bpi.presentation.transformer")->transform($_node);
        $node = $document->getEntity('node');
        $node->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $node->property('id')->getValue()), true)));
        $node->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));

        return $document;
    }

    /**
      * Display available options
      * 1. HTTP verbs
      * 2. Expected media type entities in input/output
      *
      * @Rest\Options("/node/item/{id}")
      */
    public function nodeItemOptionsAction($id)
    {
        $options = array(
              'GET' => array(
                    'output' => array(
                          'entities' => array(
                                'node'
                          )
                    )
              ),
              'OPTIONS' => array(),
              'POST' => array(
                    'input' => array(
                          'entities' => array(
                                'agency',
                                'resource',
                                'profile',
                          )
                    ),
                    'output' => array(
                          'entities' => array(
                                'node'
                          )
                    )
              )
        );
        $headers = array('Allow' => implode(', ', array_keys($options)));
        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Syndicate new revision of node
     *
     * @Rest\Post("/node/item/{id}")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="201")
     */
    public function postNodeRevisionAction($id)
    {
        $extractor = new Extractor($this->getDocument());

        $revision = $this->get('domain.push_service')->pushRevision(
            new \Bpi\ApiBundle\Domain\ValueObject\NodeId($id),
            $extractor->extract('agency.author'),
            $extractor->extract('resource')
        );

        return $this->get("bpi.presentation.transformer")->transform($revision);
    }

    /**
     * Syndicate new content
     *
     * @Rest\Post("/node")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="201")
     */
    public function postNodeAction()
    {
        /** check request body size, must be smaller than 10MB **/
        if (strlen($this->getRequest()->getContent()) > 10485760)
            throw new HttpException(413, "Request entity too large");

        $extractor = new Extractor($this->getDocument());
        $node = $this->get('domain.push_service')->push(
            $extractor->extract('agency.author'),
            $extractor->extract('resource'),
            $extractor->extract('profile')
        );

        return $this->get("bpi.presentation.transformer")->transform($node);
    }

    /**
     * Link file with node
     * Filename will overwrite existing one if it has previously set
     *
     * @Rest\Put("/node/{node_id}/asset/{filename}")
     * @Rest\View(statusCode="204")
     */
    public function putNodeAssetAction($node_id, $filename)
    {
        $node = $this->getRepository('BpiApiBundle:Aggregate\Node')->find($node_id);
        if (is_null($node))
            throw new HttpException(404, 'No such node ID exists');

        $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('assets');
        $file = new \Gaufrette\File($filename, $filesystem);
        $result = $file->setContent($this->getRequest()->getContent(), array('title' => 'test_title'));

        if (false === $result)
            throw new HttpException(500, 'Unable to store requested file');

        $node->allocateFile($file);

        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($node);
        $dm->flush();

        return new Response('', 204);
    }

    /**
     * Output media asset
     *
     * @Rest\Put("/asset/{filename}")
     * @Rest\View(statusCode="200")
     */
    public function getAssetAction($filename)
    {
        /**
         * @todo implementation
         */
    }

    /**
     * For testing purposes. Echoes back sent request
     *
     * @Rest\Get("/tools/echo")
     */
    public function echoAction()
    {
        $view = $this->view($this->get('request')->getContent(), 200);
        return $this->handleView($view);
    }
}

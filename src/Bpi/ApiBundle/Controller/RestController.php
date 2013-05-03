<?php

namespace Bpi\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Extractor;
use Bpi\ApiBundle\Domain\Entity\History;

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
     * @return \Bpi\RestMediaTypeBundle\Document
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
     * @Rest\View()
     */
    public function indexAction()
    {
        $document = new Document();

        // Node resource
        $node = $document->createRootEntity('resource', 'node');
        $hypermedia = $document->createHypermediaSection();
        $node->setHypermedia($hypermedia);
        $hypermedia->addQuery($document->createQuery(
            'item',
            $this->get('router')->generate('node_resource', array(), true),
            array('id'),
            'Find a node by ID'
        ));

        $hypermedia->addQuery($document->createQuery('filter', 'xyz', array('name', 'title'), 'Filtration'));
        $hypermedia->addLink($document->createLink(
            'self',
            $this->get('router')->generate('node_resource', array(), true),
            'Node resource'
        ));

        $hypermedia->addLink($document->createLink(
            'collection',
            $this->get('router')->generate('list', array(), true),
            'Node collection'
        ));

        $hypermedia->addTemplate($template = $document->createTemplate(
            'push',
            $this->get('router')->generate('node_resource', array(), true),
            'Template for pushing node content'
        ));

        $template->createField('title');
        $template->createField('body');
        $template->createField('teaser');
        $template->createField('type');
        $template->createField('creation');
        $template->createField('category');
        $template->createField('audience');
        $template->createField('editable');
        $template->createField('authorship');
        $template->createField('agency_id');
        $template->createField('local_id');
        $template->createField('firstname');
        $template->createField('lastname');

        // Profile resource
        $profile = $document->createRootEntity('resource', 'profile');
        $profile_hypermedia = $document->createHypermediaSection();
        $profile->setHypermedia($profile_hypermedia);
        $profile_hypermedia->addLink($document->createLink(
            'dictionary',
            $this->get('router')->generate('profile_dictionary', array(), true),
            'Profile items dictionary'
        ));

        $hypermedia->addQuery($document->createQuery(
            'syndicated',
            $this->get('router')->generate('node_syndicated', array(), true),
            array('id'),
            'Notify service about node syndication'
        ));

        return $document;
    }

     /**
     * Default node listing
     *
     * @Rest\Get("/node/collection")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="200")
     */
    public function listAction()
    {
        $node_query = new \Bpi\ApiBundle\Domain\Entity\NodeQuery();
        $node_query->sort('ctime', 'desc');
        $node_query->amount(20);
        if ($amount = $this->getRequest()->query->get('amount', false)) {
            $node_query->amount($amount);
        }

        if ($offset = $this->getRequest()->query->get('offset', false)) {
            $node_query->offset($offset);
        }

        if ($filter = $this->getRequest()->query->get('filter', false)) {
            foreach($filter as $field => $value)
                $node_query->filter($field, $value);
        }

        if ($sort = $this->getRequest()->query->get('sort', false)) {
            foreach($sort as $field => $order)
                $node_query->sort($field, $order);
        }

        $node_collection = $this->getRepository('BpiApiBundle:Aggregate\Node')->findByNodesQuery($node_query);

        $document = $this->get("bpi.presentation.transformer")->transformMany($node_collection);
        $router = $this->get('router');
        $document->walkEntities(function($e) use ($document, $router) {
            $hypermedia = $document->createHypermediaSection();
            $e->setHypermedia($hypermedia);
            $hypermedia->addLink($document->createLink('self', $router->generate('node', array('id' => $e->property('id')->getValue()), true)));
            $hypermedia->addLink($document->createLink('collection', $router->generate('list', array(), true)));

            // @todo: implementation
            //$hypermedia->addLink($document->createLink('assets', $router->generate('put_node_asset', array('node_id' => $e->property('id')->getValue(), 'filename' => ''), true)));
        });

        // Collection description
        $collection = $document->createEntity('collection');
        $document->prependEntity($collection);
        $hypermedia = $document->createHypermediaSection();
        $collection->setHypermedia($hypermedia);
        $hypermedia->addLink($document->createLink('canonical', $router->generate('list', array(), true)));
        $hypermedia->addLink($document->createLink('self', $router->generate('list', $this->getRequest()->query->all(), true)));
        $hypermedia->addQuery($document->createQuery(
            'refinement',
            $this->get('router')->generate('list', array(), true),
            array(
                'amount',
                'offset',
                $document->createQueryParameter('filter')->setMultiple(),
                $document->createQueryParameter('sort')->setMultiple(),
            ),
            'List refinements'
        ));

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
                    'action' => 'List of nodes',
                    'output' => array(
                          'entities' => array(
                                'node'
                          )
                    )
              ),
              'POST' => array(
                    'action' => 'Node list query',
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
              ),
              'OPTIONS' => array('action' => 'List available options'),
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
     * @Rest\View
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

        $hypermedia = $document->createHypermediaSection();
        $node = $document->currentEntity();
        $node->setHypermedia($hypermedia);

        //$node = $document->getEntity('node');
        $hypermedia->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $node->property('id')->getValue()), true)));
        $hypermedia->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));

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
                    'action' => 'Node item',
                    'output' => array(
                          'entities' => array(
                                'node'
                          )
                    )
              ),
              'POST' => array(
                    'action' => 'Post node revision',
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
              ),
              'OPTIONS' => array('action' => 'List available options'),
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
            $extractor->extract('author'),
            $extractor->extract('resource'),
            $extractor->extract('params')
        );

        return $this->get("bpi.presentation.transformer")->transform($revision);
    }

    /**
     * Create form to make validation
     *
     * @param array $data
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    protected function _isValidForPushNode(array $data)
    {
        // @todo move somewhere all this validation stuff

        $node = new Constraints\Collection(array(
            'allowExtraFields' => true,
            'fields' => array(
                // Author
                'agency_id' => array(
                    new Constraints\NotBlank()
                ),
                'local_id' => array(
                    new Constraints\NotBlank()
                ),
                'firstname' => array(
                    new Constraints\Length(array('min' => 2, 'max' => 100))
                ),
                'lastname' => array(
                    new Constraints\Length(array('min' => 2, 'max' => 100))
                ),
                // Resource
                'title' => array(
                    new Constraints\Length(array('min' => 2, 'max' => 500))
                ),
                'body' => array(
                    new Constraints\Length(array('min' => 2))
                ),
                'teaser' => array(
                    new Constraints\Length(array('min' => 2, 'max' => 5000))
                ),
                'creation' => array(
                    //@todo validate against DateTime::W3C format
                    new Constraints\NotBlank()
                ),
                'type' => array(
                    new Constraints\NotBlank()
                ),
                // profile; tags, yearwheel - compulsory
                'category' => array(
                    new Constraints\Length(array('min' => 2, 'max' => 100))
                ),
                'audience' => array(
                    new Constraints\Length(array('min' => 2, 'max' => 100))
                ),
                // params
                'editable' => array(
                    new Constraints\Range(array('min' => 0, 'max' => 1))
                ),
                'authorship' => array(
                    new Constraints\Range(array('min' => 0, 'max' => 1))
                ),
            )
        ));

        $validator = $this->container->get('validator');
        return $validator->validateValue($data, $node);
    }

    /**
     * Push new content
     *
     * @Rest\Post("/node")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="201")
     */
    public function postNodeAction()
    {
        /** check request body size, must be smaller than 10MB **/
        if (strlen($this->getRequest()->getContent()) > 10485760)
            throw new HttpException(413, "Request entity too large");

        // request validation
        $violations = $this->_isValidForPushNode($this->getRequest()->request->all());
        if (count($violations))
            throw new HttpException(422, (string) $violations);

        $author = new \Bpi\ApiBundle\Domain\Entity\Author(
            new \Bpi\ApiBundle\Domain\ValueObject\AgencyId($this->getRequest()->get('agency_id')),
            $this->getRequest()->get('local_author_id'),
            $this->getRequest()->get('firstname'),
            $this->getRequest()->get('lastname')
        );

        $resource = new \Bpi\ApiBundle\Domain\Factory\ResourceBuilder();
        $resource->title('title')
            ->body('body')
            ->teaser('teaser')
            ->ctime(\DateTime::createFromFormat(\DateTime::W3C, $this->getRequest()->get('creation')))
        ;

        $profile = new \Bpi\ApiBundle\Domain\Entity\Profile(
            new \Bpi\ApiBundle\Domain\ValueObject\Audience($this->getRequest()->get('audience')),
            new \Bpi\ApiBundle\Domain\ValueObject\Category($this->getRequest()->get('category'))
        );

        $params = new \Bpi\ApiBundle\Domain\Aggregate\Params();
        $params->add(new \Bpi\ApiBundle\Domain\ValueObject\Param\Authorship(
            $this->getRequest()->get('authorship')
        ));
        $params->add(new \Bpi\ApiBundle\Domain\ValueObject\Param\Editable(
            $this->getRequest()->get('editable')
        ));

        $node = $this->get('domain.push_service')->push(
            $author,
            $resource,
            $profile,
            $params
        );

        return $this->get("bpi.presentation.transformer")->transform($node);
    }

     /**
     * Asset options
     *
     * @Rest\Options("/node/{node_id}/asset")
     * @Rest\View
     */
    public function nodeAssetOptionsAction($node_id)
    {
        $options = array(
              'PUT' => array(
                    'action' => 'Add asset to specific node',
                    'input' => array(
                          'entities' => array(
                                'binary file',
                          )
                    ),
              ),
              'OPTIONS' => array('action' => 'List available options'),
        );
        $headers = array('Allow' => implode(', ', array_keys($options)));
        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Node options
     *
     * @Rest\Options("/node")
     * @Rest\View(statusCode="200")
     */
    public function nodeOptionsAction()
    {
        $options = array(
              'POST' => array(
                    'action' => 'Push new node',
                    'template' => array(
                    ),
              ),
              'OPTIONS' => array('action' => 'List available options'),
        );
        $headers = array('Allow' => implode(', ', array_keys($options)));
        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Asset options
     *
     * @Rest\Get("/node")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface2.html.twig")
     */
    public function nodeResourceAction()
    {
        $document = new Document();
        $entity = $document->createRootEntity('node');
        $controls = $document->createHypermediaSection();
        $entity->setHypermedia($controls);
        $controls->addQuery($document->createQuery('search', 'abc', array('id'), 'Find a node by ID'));
        $controls->addQuery($document->createQuery('filter', 'xyz', array('name', 'title'), 'Filtration'));
        $controls->addLink($document->createLink('self', 'abc'));
        $controls->addLink($document->createLink('collection', 'abc'));

//        $entity->addLink($document->createLink($rel, $href));

//        $node = $document->getEntity('node');
//        $node->addLink($document->createLink('self', $this->get('router')->generate('node', array('id' => $node->property('id')->getValue()), true)));
//        $node->addLink($document->createLink('collection', $this->get('router')->generate('list', array(), true)));

        return $document;

        $contnts = '<bpi version="0.2" xmlns="urn:appstate" xmlns:description="urn:description">
	<resources>
		<resource name="node" href="/node">
			<link rel="collection" href="/node/collection" />
			<link rel="template" href="/node/template" />
			<query rel="item" href="...">
				<param name="id"></param>
			</query>
		</resource>
		<resource name="revision" url="/revision">
			<link rel="template" href="/revision/template" />
		</resource>
		<resource name="asset" href="/asset" />
		<resource name="category" href="/node/profile/category">
			<link rel="collection" href="/node/profile/category/collection" />
		</resource>
		<resource name="audience" href="/node/profile/audience">
			<link rel="collection" href="/node/profile/audience/collection" />
		</resource>
	</resources>
</bpi>';
        $view = $this->view($contnts, 200);
        $view->setTemplate('BpiApiBundle:Rest:testinterface2.html.twig');
        return $this->handleView($view);
    }

    /**
     * Only for live documentation
     *
     * @Rest\Get("/node/{node_id}/asset")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="200")
     */
    public function getNodeAssetAction($node_id)
    {

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
     * Get profile dictionary
     *
     * @Rest\Get("/profile/dictionary", name="profile_dictionary")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig")
     */
    public function profileDictionaryAction()
    {
        $dictionary = $this->get('domain.profile_service')->provideDictionary();
        $document = $this->get("bpi.presentation.transformer")->transform($dictionary);
        return $document;
    }

    /**
     * Get profile dictionary options
     *
     * @Rest\Options("/profile_dictionary")
     * @Rest\View(statusCode="200")
     */
    public function profileDictionaryOptionsAction()
    {
        $options = array(
              'GET' => array(
                    'action' => 'Get profile dictionary',
                    'output' => array(
                          'entities' => array(
                                'profile_dictionary',
                          )
                    ),
              ),
              'OPTIONS' => array('action' => 'List available options'),
        );
        $headers = array('Allow' => implode(', ', array_keys($options)));
        return $this->view($options, 200, $headers);
    }

    /**
     * For testing purposes. Echoes back sent request
     *
     * @Rest\Get("/tools/echo")
     * @Rest\View(statusCode="200")
     */
    public function echoAction()
    {
        return $this->view($this->get('request')->getContent(), 200);
    }

    /**
     * Static documentation for the service
     *
     * @Rest\Get("/doc/{page}")
     * @Rest\View(template="BpiApiBundle:Rest:static_doc.html.twig")
     * @param string $page
     */
    public function docAction($page)
    {
        try {
            $file = $this->get('kernel')->locateResource('@BpiApiBundle/Resources/doc/'.$page.'.md');
            return $this->view(file_get_contents($file));
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * Mark node as syndicated
     *
     * @Rest\Get("/node/syndicated")
     * @Rest\View(statusCode="200")
     */
    public function nodeSyndicatedAction()
    {
      $id = $this->getRequest()->get('id');

      // @todo get agency id from auth
      $agency = $this->getRepository('BpiApiBundle:Aggregate\Agency')->findOneBy(array('name'=>'Aarhus Kommunes Biblioteker'));
      $agencyId = $agency->getAgencyId()->id();

      $node = $this->getRepository('BpiApiBundle:Aggregate\Node')->find($id);
      $log = new History($node, $agencyId, new \DateTime(), 'syndicate');

      $dm = $this->get('doctrine.odm.mongodb.document_manager');
      $dm->persist($log);
      $dm->flush($log);

      // @todo Add check if node exists
    }
}

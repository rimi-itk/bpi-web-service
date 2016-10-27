<?php

namespace Bpi\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\File as BpiFile;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;

/**
 * Main entry point for REST requests
 */
class RestController extends FOSRestController
{
    /**
     * Main page of API redirects to human representation of entry point
     *
     * @Rest\Get("/")
     * @Rest\View()
     */
    public function indexAction()
    {
        $document = $this->get('bpi.presentation.document');

        // Node resource
        $node = $document->createRootEntity('resource', 'node');
        $hypermedia = $document->createHypermediaSection();
        $node->setHypermedia($hypermedia);
        $hypermedia->addQuery(
            $document->createQuery(
                'item',
                $this->get('router')->generate('node_resource', array(), true),
                array('id'),
                'Find a node by ID'
            )
        );

        $hypermedia->addLink(
            $document->createLink(
                'canonical',
                $this->get('router')->generate('node_resource', array(), true),
                'Node resource'
            )
        );

        $hypermedia->addLink(
            $document->createLink(
                'collection',
                $this->get('router')->generate('list', array(), true),
                'Node collection'
            )
        );

        $hypermedia->addTemplate(
            $template = $document->createTemplate(
                'push',
                $this->get('router')->generate('node_resource', array(), true),
                'Template for pushing node content'
            )
        );

        $hypermedia->addQuery(
            $document->createQuery(
                'statistics',
                $this->get('router')->generate('statistics', array(), true),
                array('dateFrom', 'dateTo'),
                'Statistics for date range'
            )
        );

        $hypermedia->addQuery(
            $document->createQuery(
                'syndicated',
                $this->get('router')->generate('node_syndicated', array(), true),
                array('id'),
                'Notify service about node syndication'
            )
        );

        $hypermedia->addQuery(
            $document->createQuery(
                'delete',
                $this->get('router')->generate('node_delete', array(), true),
                array('id'),
                'Mark node as deleted'
            )
        );

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
        $template->createField('assets');
        $template->createField('related_materials');
        $template->createField('tags');
        $template->createField('url');
        $template->createField('data', 'json');

        // Profile resource
        $profile = $document->createRootEntity('resource', 'profile');
        $profile_hypermedia = $document->createHypermediaSection();
        $profile->setHypermedia($profile_hypermedia);
        $profile_hypermedia->addLink(
            $document->createLink(
                'dictionary',
                $this->get('router')->generate('profile_dictionary', array(), true),
                'Profile items dictionary'
            )
        );

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
        $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');
        $node_query = new \Bpi\ApiBundle\Domain\Entity\NodeQuery();
        $node_query->amount(20);
        if (false !== ($amount = $this->getRequest()->query->get('amount', false))) {
            $node_query->amount($amount);
        }

        if (false !== ($offset = $this->getRequest()->query->get('offset', false))) {
            $node_query->offset($offset);
        }

        if (false !== ($search = $this->getRequest()->query->get('search', false))) {
            $node_query->search($search);
        }

        $filters = array();
        $logicalOperator = '';
        if (false !== ($filter = $this->getRequest()->query->get('filter', false))) {
            foreach ($filter as $field => $value) {
                if ($field == 'type' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {continue; }
                        $filters['type'][] = $val;
                    }
                }
                if ($field == 'category' && !empty($value)) {
                    foreach ($value as $val) {
                        $category = $this->getRepository('BpiApiBundle:Entity\Category')->findOneBy(array('category' => $val));
                        if (empty($category)) {continue; }
                        $filters['category'][] = $category;
                    }
                }
                if ($field == 'audience' && !empty($value)) {
                    foreach ($value as $val) {
                        $audience = $this->getRepository('BpiApiBundle:Entity\Audience')->findOneBy(array('audience' => $val));
                        if (empty($audience)) {continue; }
                        $filters['audience'][] = $audience;
                    }
                }
                if ($field == 'agency_id' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {continue; }
                        $filters['agency_id'][] = $val;
                    }
                }
                if ($field == 'tags' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {continue; }
                        $filters['tags'][] = $val;
                    }
                }

                if ($field == 'author' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $filters['author'][] = $val;
                    }
                }

                if ($field == 'channels' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {continue; }
                        $filters['channels'][] = $val;
                    }
                }
            }
            if (isset($filter['agencyInternal'])) {
                $filters['agency_internal'][] = $filter['agencyInternal'];
            }
            if (isset($filter['logicalOperator']) && !empty($filter['logicalOperator'])) {
                $logicalOperator = $filter['logicalOperator'];
            }
        }
        $availableFacets = $facetRepository->getFacetsByRequest($filters, $logicalOperator);
        $node_query->filter($availableFacets->nodeIds);

        if (false !== ($sort = $this->getRequest()->query->get('sort', false))) {
            foreach ($sort as $field => $order)
                $node_query->sort($field, $order);
        } else {
            $node_query->sort('pushed', 'desc');
        }
        $node_collection = $this->getRepository('BpiApiBundle:Aggregate\Node')->findByNodesQuery($node_query);
        $agency_id = new AgencyId($this->getUser()->getAgencyId()->id());
        foreach ($node_collection as $node) {
          $node->defineAgencyContext($agency_id);
        }

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transformMany($node_collection);
        $router = $this->get('router');
        $document->walkEntities(
            function ($e) use ($document, $router) {
                $hypermedia = $document->createHypermediaSection();
                $e->setHypermedia($hypermedia);
                $hypermedia->addLink(
                    $document->createLink(
                        'self',
                        $router->generate('node', array('id' => $e->property('id')->getValue()), true)
                    )
                );
                $hypermedia->addLink($document->createLink('collection', $router->generate('list', array(), true)));

                // @todo: implementation
                //$hypermedia->addLink($document->createLink('assets', $router->generate('put_node_asset', array('node_id' => $e->property('id')->getValue(), 'filename' => ''), true)));
            }
        );
        // Collection description
        $collection = $document->createEntity('collection');
        $collection->addProperty(
            $document->createProperty(
                'total',
                'integer',
                $node_query->total
            )
        );
        $document->prependEntity($collection);
        $hypermedia = $document->createHypermediaSection();
        $collection->setHypermedia($hypermedia);
        $hypermedia->addLink($document->createLink('canonical', $router->generate('list', array(), true)));
        $hypermedia->addLink(
            $document->createLink('self', $router->generate('list', $this->getRequest()->query->all(), true))
        );
        $hypermedia->addQuery(
            $document->createQuery(
                'refinement',
                $this->get('router')->generate('list', array(), true),
                array(
                    'amount',
                    'offset',
                    'search',
                    $document->createQueryParameter('filter')->setMultiple(),
                    $document->createQueryParameter('sort')->setMultiple(),
                ),
                'List refinements'
            )
        );

        // Prepare facets for xml.
        foreach ($availableFacets->facets as $facetName => $facet) {
            $facetsXml = $document->createEntity('facet', $facetName);
            $result = array();
            foreach ($facet as $key => $term) {
                if ($facetName == 'agency_id') {
                    $result[] = $document->createProperty(
                        $key,
                        'string',
                        $term['count'],
                        $term['agencyName']
                    );
                } elseif (isset($term['count'])) {
                  $result[] = $document->createProperty(
                    $key,
                    'string',
                    $term['count'],
                    isset($term['title']) ? $term['title'] : ''
                  );
                } else {
                    $result[] = $document->createProperty(
                        $key,
                        'string',
                        $term
                    );
                }
            }

            $facetsXml->addProperties($result);
            $document->prependEntity($facetsXml);
        }


        return $document;
    }

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
     * Shows statictic by AgencyId
     *  - Number of pushed nodes.
     *  - Number of syncidated nodes.
     *
     * @Rest\Get("/statistics")
     * @Rest\View(template="BpiApiBundle:Rest:statistics.html.twig", statusCode="200")
     */
    public function statisticsAction()
    {
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->getRequest();
        $agencyId = $this->getUser()->getAgencyId()->id();

        // @todo Add input validation
        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');

        $repo = $this->getRepository('BpiApiBundle:Entity\History');
        $stats = $repo->getStatisticsByDateRangeForAgency($dateFrom, $dateTo, $agencyId);

        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transform($stats);

        return $document;
    }

    /**
     * Display available options
     *
     * @Rest\Options("/node/collection")
     */
    public function nodeListOptionsAction()
    {
        $options = array(
            'GET' => array(
                'action' => 'List of nodes',
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
        $transformer->setDoc($this->get('bpi.presentation.document'));
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
                $doc = $this->get('bpi.presentation.document');
                $doc->appendEntity($entity = $doc->createEntity('nodes_query'));
                $entity->addProperty($doc->createProperty('amount', 'number', 10));
                $entity->addProperty($doc->createProperty('offset', 'number', 0));
                $entity->addProperty($doc->createProperty('filter[resource.title]', 'string', ''));
                $entity->addProperty($doc->createProperty('sort[ctime]', 'string', 'desc'));
                $entity->addProperty(
                    $doc->createProperty('reduce', 'string', 'initial', 'Reduce revisions to initial or latest')
                );

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

        if (!$_node) {
            throw $this->createNotFoundException();
        }
        $_node->defineAgencyContext(new AgencyId($this->getUser()->getAgencyId()->id()));
        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transform($_node);

        $hypermedia = $document->createHypermediaSection();
        $node = $document->currentEntity();
        $node->setHypermedia($hypermedia);

        $hypermedia->addLink(
            $document->createLink(
                'self',
                $this->generateUrl('node', array('id' => $node->property('id')->getValue()), true)
            )
        );
        $hypermedia->addLink($document->createLink('collection', $this->generateUrl('list', array(), true)));

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
     * Push new content
     *
     * @Rest\Post("/node")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="201")
     */
    public function postNodeAction()
    {
        $request = $this->getRequest();
        $service = $this->get('domain.push_service');
        BpiFile::$base_url = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost() . $this->getRequest()->getBasePath();
        $assets = array();

        $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');

        /** check request body size, must be smaller than 10MB **/
        if (strlen($request->getContent()) > 10485760) {
            return $this->createErrorView('Request entity too large', 413);
        }

        // request validation
        $violations = $this->_isValidForPushNode($request->request->all());
        if (count($violations)) {
            return $this->createErrorView((string) $violations, 422);
        }

        $author = new \Bpi\ApiBundle\Domain\Entity\Author(
            new \Bpi\ApiBundle\Domain\ValueObject\AgencyId($request->get('agency_id')),
            $request->get('local_author_id'),
            $request->get('firstname'),
            $request->get('lastname')
        );


        $resource = new \Bpi\ApiBundle\Domain\Factory\ResourceBuilder($this->get('router'));
        $resource
          ->type($request->get('type'))
          ->title($request->get('title'))
          ->body($request->get('body'))
          ->teaser($request->get('teaser'))
          ->url($request->get('url'))
          ->data($request->get('data'))
          ->ctime(\DateTime::createFromFormat(\DateTime::W3C, $request->get('creation')));

        // Related materials
        foreach ($request->get('related_materials', array()) as $material) {
            $resource->addMaterial($material);
        }

        // Download files and add them to resource
        $assets = new \Bpi\ApiBundle\Domain\Aggregate\Assets();
        $data = $request->get('assets', array());
        foreach ($data as $asset) {
            $bpi_file = new \Bpi\ApiBundle\Domain\Entity\File($asset);
            $bpi_file->createFile();
            $assets->addElem($bpi_file);
        }

        $profile = new \Bpi\ApiBundle\Domain\Entity\Profile();

        $params = new \Bpi\ApiBundle\Domain\Aggregate\Params();
        $params->add(
            new \Bpi\ApiBundle\Domain\ValueObject\Param\Authorship(
                $request->get('authorship')
            )
        );
        $params->add(
            new \Bpi\ApiBundle\Domain\ValueObject\Param\Editable(
                $request->get('editable')
            )
        );

        try {
            // Check for BPI ID
            if ($id = $request->get('bpi_id', false)) {
                if (!$this->getRepository('BpiApiBundle:Aggregate\Node')->find($id)) {
                    return $this->createErrorView(sprintf('Such BPI ID [%s] not found', $id), 422);
                }

                $node = $this->get('domain.push_service')
                  ->pushRevision(new NodeId($id), $author, $resource, $params, $assets);

                $facetRepository->prepareFacet($node);

                $transform = $this->get('bpi.presentation.transformer');
                $transform->setDoc($this->get('bpi.presentation.document'));
                $document = $transform->transform($node);
                return $document;

            }
            $node = $this->get('domain.push_service')
              ->push($author, $resource, $request->get('category'), $request->get('audience'), $request->get('tags'), $profile, $params, $assets);

            $transform = $this->get('bpi.presentation.transformer');
            $transform->setDoc($this->get('bpi.presentation.document'));
            $document = $transform->transform($node);
            return $document;
        } catch (\LogicException $e) {
            return $this->createErrorView($e->getMessage(), 422);
        }
    }

    /**
     *
     * @param string $contents
     * @param int $code
     * @return View
     */
    protected function createErrorView($contents, $code)
    {
        // @todo standart error format
        return $this->view($contents, $code);
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
                /* @todo
                'editable' => array(
                 * new Constraints\Type(array('type' => 'boolean'))
                 * ),
                 * 'authorship' => array(
                 * new Constraints\Type(array('type' => 'boolean'))
                 * ),
                 */
            )
        ));

        $validator = $this->container->get('validator');

        return $validator->validateValue($data, $node);
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
                'template' => array(),
            ),
            'OPTIONS' => array('action' => 'List available options'),
        );
        $headers = array('Allow' => implode(', ', array_keys($options)));

        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Node resource
     *
     * @Rest\Get("/node")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface2.html.twig")
     */
    public function nodeResourceAction()
    {
        // Handle query by node id
        if ($id = $this->getRequest()->get('id')) {
            // SDK can not handle properly redirects, so query string is used
            // @see https://github.com/symfony/symfony/issues/7929
            $params = array(
                'id' => $id,
                '_authorization' => array(
                    'agency' => $this->getUser()->getAgencyId()->id(),
                    'token' => $this->container->get('security.context')->getToken()->token
                )
            );
            return $this->redirect($this->generateUrl('node', $params));
        }

        $document = $this->get('bpi.presentation.document');
        $entity = $document->createRootEntity('node');
        $controls = $document->createHypermediaSection();
        $entity->setHypermedia($controls);
        $controls->addQuery($document->createQuery('search', 'abc', array('id'), 'Find a node by ID'));
        $controls->addQuery($document->createQuery('filter', 'xyz', array('name', 'title'), 'Filtration'));
        $controls->addLink($document->createLink('self', 'Self'));
        $controls->addLink($document->createLink('collection', 'Collection'));

        return $document;
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
        /*
         * NOT USED
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
        */
    }

    /**
     * Output media asset
     *
     * @Rest\Get("/asset/{filename}.{extension}")
     */
    public function getAssetAction($filename, $extension)
    {
        $extension = strtolower($extension);

        $file = $filename . '.' . $extension;
        $mime = 'application/octet-stream';

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;
            case 'gif':
                $mime = 'image/gif';
                break;
            case 'png':
                $mime = 'image/png';
        }
        $headers = array(
            'Content-Type' => $mime
        );

        try {
            $fs = $this->get('domain.push_service')->getFilesystem();
            $file = $fs->get($filename);
            return new Response($file->getContent(), 200, $headers);
        } catch (\Gaufrette\Exception\FileNotFound $e) {
            throw $this->createNotFoundException();
        } catch (\Exception $e) {
            return new Response('Bad file', 410);
        }
    }

    /**
     * Get profile dictionary
     *
     * @Rest\Get("/profile/dictionary", name="profile_dictionary")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig")
     */
    public function profileDictionaryAction()
    {
        $document = $this->get('bpi.presentation.document');

        $audiences = $this->getRepository('BpiApiBundle:Entity\Audience')->findAll();
        $categories = $this->getRepository('BpiApiBundle:Entity\Category')->findAll();

        foreach ($audiences as $audience) {
            $audience->transform($document);
        }

        foreach ($categories as $category) {
            $category->transform($document);
        }

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
            $file = $this->get('kernel')->locateResource('@BpiApiBundle/Resources/doc/' . $page . '.md');

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
        $agency = $this->getUser();

        $nodeRepository = $this->getRepository('BpiApiBundle:Aggregate\Node');
        $node = $nodeRepository->find($id);
        if (!$node) {
            throw $this->createNotFoundException();
        }

        if ($node->isOwner($agency)) {
            return $this->createErrorView(
                'Not Acceptable: Trying to syndicate content by owner who already did that',
                406
            );
        }

        $log = new History($node, $agency->getAgencyId()->id(), new \DateTime(), 'syndicate');

        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($log);
        $dm->flush($log);

        $nodeSyndications = $node->getSyndications();
        if (null === $nodeSyndications) {
            $node->setSyndications(1);
        } else {
            $node->setSyndications(++$nodeSyndications);
        }

        $dm->persist($node);
        $dm->flush($node);

        return new Response('', 200);
    }

    /**
     * Mark node as deleted
     *
     * @Rest\Get("/node/delete")
     * @Rest\View(statusCode="200")
     */
    public function nodeDeleteAction()
    {
        // @todo Add check if node exists

        $id = $this->getRequest()->get('id');

        $agencyId = $this->getUser()->getAgencyId()->id();

        $node = $this->getRepository('BpiApiBundle:Aggregate\Node')->delete($id, $agencyId);

        if ($node == null) {
            return new Response('This node does not belong to you', 403);
        }

        return new Response('', 200);
    }

    /**
     * Get static images
     *
     * @Rest\Get("/images/{file}.{ext}")
     * @Rest\View(statusCode="200")
     */
    public function staticImagesAction($file, $ext)
    {
        $file = __DIR__ . '/../Resources/public/images/' . $file . '.' . $ext;
        $mime = mime_content_type($file);
        $headers = array(
            'Content-Type' => $mime
        );

        return new Response(file_get_contents($file), 200, $headers);
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
}

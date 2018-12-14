<?php

namespace Bpi\ApiBundle\Controller;

use Bpi\ApiBundle\Domain\Aggregate\Assets;
use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Entity\File;
use Bpi\ApiBundle\Domain\Entity\NodeQuery;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\File as BpiFile;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;

/**
 * Main entry point for REST requests.
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
        $template->createField('data');

        // Profile resource
        $profile = $document->createRootEntity('resource', 'profile');
        $profile_hypermedia = $document->createHypermediaSection();
        $profile->setHypermedia($profile_hypermedia);
        // TODO: Fix link.
//        $profile_hypermedia->addLink(
//            $document->createLink(
//                'dictionary',
//                $this->get('router')->generate('profile_dictionary', array(), true),
//                'Profile items dictionary'
//            )
//        );

        return $document;
    }

    /**
     * Default node listing
     *
     * @Rest\Get("/node/collection")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="200")
     */
    public function listAction(Request $request)
    {
        $node_query = new NodeQuery();
        $node_query->amount(20);
        if (false !== ($amount = $request->query->get('amount', false))) {
            $node_query->amount($amount);
        }

        if (false !== ($offset = $request->query->get('offset', false))) {
            $node_query->offset($offset);
        }

        if (false !== ($search = $request->query->get('search', false))) {
            $node_query->search($search);
        }

        $filters = array();
        $logicalOperator = '';
        if (false !== ($filter = $request->query->get('filter', false))) {
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
        /** @var \Bpi\ApiBundle\Domain\Repository\FacetRepository $facetRepository */
        $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');
        $availableFacets = $facetRepository->getFacetsByRequest($filters, $logicalOperator);

        $node_query->filter($availableFacets->nodeIds);

        if (false !== ($sort = $request->query->get('sort', false))) {
            foreach ($sort as $field => $order)
                $node_query->sort($field, $order);
        } else {
            $node_query->sort('pushed', 'desc');
        }

        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $node_repository */
        $node_repository = $this->getRepository('BpiApiBundle:Aggregate\Node');
        $node_collection = $node_repository->findByNodesQuery($node_query);

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
            $document->createLink('self', $router->generate('list', $request->query->all(), true))
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
     * Shows statistics for a certain agency.
     *
     * This would return:
     *  - Number of pushed nodes.
     *  - Number of syndicated nodes.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request object.
     *
     * @Rest\Get("/statistics")
     * @Rest\View(template="BpiApiBundle:Rest:statistics.html.twig", statusCode="200")
     */
    public function statisticsAction(Request $request)
    {
        $agencyId = $this->getUser()->getAgencyId()->id();

        // @todo Add input validation
        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');

        /** @var \Bpi\ApiBundle\Domain\Repository\HistoryRepository $repo */
        $repo = $this->getRepository('BpiApiBundle:Entity\History');
        $stats = $repo->getStatisticsByDateRangeForAgency($dateFrom, $dateTo, $agencyId);

        /** @var \Bpi\ApiBundle\Transform\Presentation $transform */
        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transform($stats);

        return $document;
    }

    /**
     * Display available options.
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
     * Display node item.
     *
     * @Rest\Get("/node/item/{id}")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig")
     */
    public function nodeAction(Node $loadedNode)
    {
        if (!$loadedNode) {
            throw $this->createNotFoundException();
        }
        $loadedNode->defineAgencyContext(new AgencyId($this->getUser()->getAgencyId()->id()));
        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transform($loadedNode);

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
     * Push new content.
     *
     * @Rest\Post("/node")
     * @Rest\View(template="BpiApiBundle:Rest:testinterface.html.twig", statusCode="201")
     */
    public function postNodeAction(Request $request)
    {
        BpiFile::$base_url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

        // Validate request size.
        $returnBytes = function ($value)
        {
            switch (substr ($value, -1))
            {
                case 'K': case 'k': return (int)$value * 1024;
                case 'M': case 'm': return (int)$value * 1048576;
                case 'G': case 'g': return (int)$value * 1073741824;
                default: return $value;
            }
        };

        if (strlen($request->getContent()) > $returnBytes(ini_get('post_max_size'))) {
            return $this->createErrorView('Request entity too large', 413);
        }

        // Validate payload structure.
        $violations = $this->_isValidForPushNode($request->request->all());
        if (count($violations)) {
            return $this->createErrorView((string) $violations, 422);
        }

        $author = new \Bpi\ApiBundle\Domain\Entity\Author(
            new AgencyId($request->get('agency_id')),
            $request->get('local_author_id'),
            $request->get('firstname'),
            $request->get('lastname')
        );

        $resource = new ResourceBuilder($this->get('router'));

        $resource
          ->type($request->get('type'))
          ->title($request->get('title'))
          ->body($request->get('body'))
          ->teaser($request->get('teaser'))
          ->url($request->get('url'))
          ->data($request->get('data'))
          ->ctime(\DateTime::createFromFormat(\DateTime::W3C, $request->get('creation')));

        // Prepare related materials.
        foreach ($request->get('related_materials', array()) as $material) {
            $resource->addMaterial($material);
        }

        // Prepare assets.
        $assets = new Assets();
        $data = $request->get('assets', array());
        foreach ($data as $asset) {
            $bpi_file = new File($asset);
            $bpi_file->createFile();
            $assets->addElem($bpi_file);
        }

        // TODO: What's a profile anyway?
        $profile = new Profile();

        $params = new Params();
        $params->add(
            new Authorship(
                $request->get('authorship')
            )
        );
        $params->add(
            new Editable(
                $request->get('editable')
            )
        );

        /** @var \Bpi\ApiBundle\Domain\Service\PushService $pushService */
        $pushService = $this->get('domain.push_service');

        try {
            // Check for BPI ID
            if ($id = $request->get('bpi_id', false)) {
                if (!$this->getRepository('BpiApiBundle:Aggregate\Node')->find($id)) {
                    return $this->createErrorView(sprintf('Such BPI ID [%s] not found', $id), 422);
                }

                $node = $pushService->pushRevision(
                    new NodeId($id),
                    $author,
                    $resource,
                    $params,
                    $assets
                );

                /** @var \Bpi\ApiBundle\Domain\Repository\FacetRepository $facetRepository */
                $facetRepository = $this->getRepository('BpiApiBundle:Entity\Facet');
                $facetRepository->prepareFacet($node);

                $transform = $this->get('bpi.presentation.transformer');
                $transform->setDoc($this->get('bpi.presentation.document'));
                $document = $transform->transform($node);

                return $document;
            }

            $node = $pushService->push(
                $author,
                $resource,
                $request->get('category'),
                $request->get('audience'),
                $request->get('tags'),
                $profile,
                $params,
                $assets
            );

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

        /** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $validator = $this->container->get('validator');

        return $validator->validate($data, $node);
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
    public function nodeResourceAction(Request $request)
    {
        // Handle query by node id
        if ($id = $request->get('id')) {
            // SDK can not handle properly redirects, so query string is used
            // @see https://github.com/symfony/symfony/issues/7929
            $params = array(
                'id' => $id,
                '_authorization' => array(
                    'agency' => $this->getUser()->getAgencyId()->id(),
                    'token' => $this->container->get('security.token_storage')->getToken()->token
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
     * Mark node as syndicated
     *
     * @Rest\Get("/node/syndicated")
     * @Rest\View(statusCode="200")
     */
    public function nodeSyndicatedAction(Request $request)
    {
        $nodeId = $request->get('id');
        $agency = $this->getUser();

        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $nodeRepository */
        $nodeRepository = $this->getRepository('BpiApiBundle:Aggregate\Node');
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node $node */
        $node = $nodeRepository->find($nodeId);
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
    public function nodeDeleteAction(Request $request)
    {
        // @todo Add check if node exists

        $nodeId = $request->get('id');

        $agencyId = $this->getUser()->getAgencyId()->id();

        $node = $this->getRepository('BpiApiBundle:Aggregate\Node')->delete($nodeId, $agencyId);

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
}

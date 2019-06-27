<?php

namespace Bpi\ApiBundle\Controller;

use Bpi\ApiBundle\Domain\Aggregate\Assets;
use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\Entity\Audience;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Entity\Category;
use Bpi\ApiBundle\Domain\Entity\Facet;
use Bpi\ApiBundle\Domain\Entity\File;
use Bpi\ApiBundle\Domain\Entity\NodeQuery;
use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

use Bpi\ApiBundle\Domain\Entity\History;
use Bpi\ApiBundle\Domain\Entity\File as BpiFile;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;

/**
 * Main entry point for REST requests.
 */
class RestController extends FOSRestController
{
    const NODE_LIST_AMOUNT = 10;

    /**
     * Handles display of general API schema.
     *
     * @Rest\Get("/")
     * @Rest\View(statusCode="200")
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     *
     * TODO: This serves no practical function.
     *
     * @deprecated
     */
    public function indexAction()
    {
        /** @var \Bpi\RestMediaTypeBundle\Document $document */
        $document = $this->get('bpi.presentation.document');

        // Node resource
        $node = $document->createRootEntity('resource', 'node');
        $hypermedia = $document->createHypermediaSection();
        $node->setHypermedia($hypermedia);
        $hypermedia->addQuery(
            $document->createQuery(
                'item',
                $this->get('router')->generate('node_resource', [], true),
                ['id'],
                'Find a node by ID'
            )
        );

        $hypermedia->addLink(
            $document->createLink(
                'canonical',
                $this->get('router')->generate('node_resource', [], true),
                'Node resource'
            )
        );

        $hypermedia->addLink(
            $document->createLink(
                'collection',
                $this->get('router')->generate('list', [], true),
                'Node collection'
            )
        );

        $hypermedia->addTemplate(
            $template = $document->createTemplate(
                'push',
                $this->get('router')->generate('node_resource', [], true),
                'Template for pushing node content'
            )
        );

        $hypermedia->addQuery(
            $document->createQuery(
                'statistics',
                $this->get('router')->generate('statistics', [], true),
                ['dateFrom', 'dateTo'],
                'Statistics for date range'
            )
        );

        $hypermedia->addQuery(
            $document->createQuery(
                'syndicated',
                $this->get('router')->generate('node_syndicated', [], true),
                ['id'],
                'Notify service about node syndication'
            )
        );

        $hypermedia->addQuery(
            $document->createQuery(
                'delete',
                $this->get('router')->generate('node_delete', [], true),
                ['id'],
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
        $profile_hypermedia->addLink(
            $document->createLink(
                'dictionary',
                $this->get('router')->generate('profile_dictionary', [], true),
                'Profile items dictionary'
            )
        );

        return $document;
    }

    /**
     * Handles node listing.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Get("/node/collection")
     * @Rest\View(statusCode="200")
     */
    public function listAction(Request $request)
    {
        $node_query = new NodeQuery();

        $node_query->amount($request->query->get('amount', self::NODE_LIST_AMOUNT));
        $node_query->offset($request->query->get('offset', 0));

        if ($search = $request->query->get('search')) {
            $node_query->search($search);
        }

        $filters = [];
        $logicalOperator = '';
        if ($filter = $request->query->get('filter', [])) {
            foreach ($filter as $field => $value) {
                if ($field == 'type' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $filters['type'][] = $val;
                    }
                }
                if ($field == 'category' && !empty($value)) {
                    foreach ($value as $val) {
                        $category = $this
                            ->getRepository(Category::class)
                            ->findOneBy(['category' => $val]);
                        if (empty($category)) {
                            continue;
                        }
                        $filters['category'][] = $category;
                    }
                }
                if ($field == 'audience' && !empty($value)) {
                    foreach ($value as $val) {
                        $audience = $this
                            ->getRepository(Audience::class)
                            ->findOneBy(['audience' => $val]);
                        if (empty($audience)) {
                            continue;
                        }
                        $filters['audience'][] = $audience;
                    }
                }
                if ($field == 'agency_id' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
                        $filters['agency_id'][] = $val;
                    }
                }
                if ($field == 'tags' && !empty($value)) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            continue;
                        }
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
                        if (empty($val)) {
                            continue;
                        }
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
        $facetRepository = $this->getRepository(Facet::class);
        $availableFacets = $facetRepository->getFacetsByRequest($filters, $logicalOperator);

        $node_query->filter($availableFacets->nodeIds);

        if ($sort = $request->query->get('sort', [])) {
            foreach ($sort as $field => $order) {
                $node_query->sort($field, $order);
            }
        } else {
            $node_query->sort('pushed', 'desc');
        }

        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $node_repository */
        $node_repository = $this->getRepository(Node::class);
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Node[] $node_collection */
        $node_collection = $node_repository->findByNodesQuery($node_query);

        $agency_id = new AgencyId($this->getUser()->getAgencyId()->id());
        foreach ($node_collection as $node) {
            $node->defineAgencyContext($agency_id);
        }

        /** @var \Bpi\ApiBundle\Transform\Presentation $transform */
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
                        $router->generate('node', ['id' => $e->property('id')->getValue()], true)
                    )
                );
                $hypermedia
                    ->addLink(
                        $document->createLink(
                            'collection',
                            $router->generate('list', [], true)
                        )
                    );
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
        $hypermedia->addLink($document->createLink('canonical', $router->generate('list', [], true)));
        $hypermedia->addLink(
            $document->createLink('self', $router->generate('list', $request->query->all(), true))
        );
        $hypermedia->addQuery(
            $document->createQuery(
                'refinement',
                $this->get('router')->generate('list', [], true),
                [
                    'amount',
                    'offset',
                    'search',
                    $document->createQueryParameter('filter')->setMultiple(),
                    $document->createQueryParameter('sort')->setMultiple(),
                ],
                'List refinements'
            )
        );

        // Prepare facets for xml.
        foreach ($availableFacets->facets as $facetName => $facet) {
            $facetsXml = $document->createEntity('facet', $facetName);
            $result = [];
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
     * Gets entity repository.
     *
     * @param string $name Repository name.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getRepository($name)
    {
        return $this->get('doctrine_mongodb')->getRepository($name);
    }

    /**
     * Handles statistics fetch.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object.
     *
     * @Rest\Get("/statistics")
     * @Rest\View(statusCode="200")
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     *
     * @deprecated
     */
    public function statisticsAction(Request $request)
    {
        $agencyId = $this->getUser()->getAgencyId()->id();

        // @todo Add input validation
        $dateFrom = new \DateTime($request->get('dateFrom', date('Y-m-d')));
        $dateTo = (new \DateTime($request->get('dateTo', date('Y-m-d'))))->modify('+23 hours 59 minutes');

        /** @var \Bpi\ApiBundle\Domain\Repository\HistoryRepository $repo */
        $repo = $this->getRepository('BpiApiBundle:Entity\History');
        $stats = $repo->getStatisticsByDateRangeForAgency($dateFrom, $dateTo, [$agencyId]);

        /** @var \Bpi\ApiBundle\Transform\Presentation $transform */
        $transform = $this->get('bpi.presentation.transformer');
        $transform->setDoc($this->get('bpi.presentation.document'));
        $document = $transform->transform($stats);

        return $document;
    }

    /**
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @Rest\Get("/statisticsExtended")
     * @Rest\View(statusCode="200")
     *
     * @return \Bpi\RestMediaTypeBundle\XmlResponse
     */
    public function statisticsExtendedAction(Request $request) {
        $dateFrom = new \DateTime($request->get('dateFrom', date('Y-m-d')));
        $dateTo = (new \DateTime($request->get('dateTo', date('Y-m-d'))))->modify('+23 hours 59 minutes');
        $agencies = explode(',', $request->get('agencies', ''));

        /** @var \Bpi\ApiBundle\Domain\Repository\HistoryRepository $repository */
        $repository = $this->getRepository('BpiApiBundle:Entity\History');
        $stats = $repository->getActivity(
            $dateFrom,
            $dateTo,
            'push',
            'agency'
        );
    }

    /**
     * Display available options.
     *
     * @Rest\Options("/node/collection")
     *
     * TODO: This serves no practical function.
     * @deprecated
     */
    public function nodeListOptionsAction()
    {
        $options = [
            'GET' => [
                'action' => 'List of nodes',
            ],
            'OPTIONS' => ['action' => 'List available options'],
        ];
        $headers = ['Allow' => implode(', ', array_keys($options))];

        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Display node item.
     *
     * @Rest\Get("/node/item/{id}")
     * @Rest\View(statusCode="200")
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
                $this->generateUrl('node', ['id' => $node->property('id')->getValue()], true)
            )
        );
        $hypermedia->addLink($document->createLink('collection', $this->generateUrl('list', [], true)));

        return $document;
    }

    /**
     * Display available options
     * 1. HTTP verbs
     * 2. Expected media type entities in input/output
     *
     * @Rest\Options("/node/item/{id}")
     *
     * TODO: This serves no practical function.
     * @deprecated
     */
    public function nodeItemOptionsAction($id)
    {
        $options = [
            'GET' => [
                'action' => 'Node item',
                'output' => [
                    'entities' => [
                        'node',
                    ],
                ],
            ],
            'POST' => [
                'action' => 'Post node revision',
                'input' => [
                    'entities' => [
                        'agency',
                        'resource',
                        'profile',
                    ],
                ],
                'output' => [
                    'entities' => [
                        'node',
                    ],
                ],
            ],
            'OPTIONS' => ['action' => 'List available options'],
        ];
        $headers = ['Allow' => implode(', ', array_keys($options))];

        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Push new content.
     *
     * @Rest\Post("/node")
     * @Rest\View(statusCode="201")
     */
    public function postNodeAction(Request $request)
    {
        BpiFile::$base_url = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();

        // Validate request size.
        $returnBytes = function ($value) {
            switch (substr($value, -1)) {
                case 'K':
                case 'k':
                    return (int)$value * 1024;
                case 'M':
                case 'm':
                    return (int)$value * 1048576;
                case 'G':
                case 'g':
                    return (int)$value * 1073741824;
                default:
                    return $value;
            }
        };

        if (strlen($request->getContent()) > $returnBytes(ini_get('post_max_size'))) {
            return $this->createErrorView('Request entity too large', 413);
        }

        // Validate payload structure.
        $violations = $this->isValidForPushNode($request->request->all());
        if (count($violations)) {
            return $this->createErrorView((string)$violations, 422);
        }

        $author = new Author(
            new AgencyId($request->get('agency_id')),
            $request->get('local_author_id'),
            $request->get('lastname'),
            $request->get('firstname')
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
        foreach ($request->get('related_materials', []) as $material) {
            $resource->addMaterial($material);
        }

        // Prepare assets.
        $assets = new Assets();
        $data = $request->get('assets', []);
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
                $request->get('authorship', 0)
            )
        );
        $params->add(
            new Editable(
                $request->get('editable', 0)
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
     * Prepares a general purpose view response.
     *
     * @param string $contents
     * @param int $code
     *
     * @return \FOS\RestBundle\View\View
     */
    protected function createErrorView($contents, $code)
    {
        return $this->view($contents, $code);
    }

    /**
     * Validates node payload.
     *
     * @param array $data Node payload.
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function isValidForPushNode(array $data)
    {
        // @todo move somewhere all this validation stuff
        $node = new Constraints\Collection(
            [
                'allowExtraFields' => true,
                'fields' => [
                    // Author
                    'agency_id' => [
                        new Constraints\NotBlank(),
                    ],
                    'local_id' => [
                        new Constraints\NotBlank(),
                    ],
                    'firstname' => [
                        new Constraints\Length(['min' => 2, 'max' => 100]),
                    ],
                    'lastname' => [
                        new Constraints\Length(['min' => 2, 'max' => 100]),
                    ],
                    // Resource
                    'title' => [
                        new Constraints\Length(['min' => 2, 'max' => 500]),
                    ],
                    'body' => [
                        new Constraints\Length(['min' => 2]),
                    ],
                    'teaser' => [
                        new Constraints\Length(['min' => 2, 'max' => 5000]),
                    ],
                    'creation' => [
                        //@todo validate against DateTime::W3C format
                        new Constraints\NotBlank(),
                    ],
                    'type' => [
                        new Constraints\NotBlank(),
                    ],
                    // profile; tags, yearwheel - compulsory
                    'category' => [
                        new Constraints\Length(['min' => 2, 'max' => 100]),
                    ],
                    'audience' => [
                        new Constraints\Length(['min' => 2, 'max' => 100]),
                    ],
                    // TODO: Validate params (editable/authorship).
                ],
            ]
        );

        /** @var \Symfony\Component\Validator\Validator\ValidatorInterface $validator */
        $validator = $this->container->get('validator');

        return $validator->validate($data, $node);
    }

    /**
     * Asset options
     *
     * @Rest\Options("/node/{node_id}/asset")
     * @Rest\View
     *
     * TODO: This serves no practical function.
     * @deprecated
     */
    public function nodeAssetOptionsAction($node_id)
    {
        $options = [
            'PUT' => [
                'action' => 'Add asset to specific node',
                'input' => [
                    'entities' => [
                        'binary file',
                    ],
                ],
            ],
            'OPTIONS' => ['action' => 'List available options'],
        ];
        $headers = ['Allow' => implode(', ', array_keys($options))];

        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Node options
     *
     * @Rest\Options("/node")
     * @Rest\View(statusCode="200")
     *
     * TODO: This serves no practical function.
     * @deprecated
     */
    public function nodeOptionsAction()
    {
        $options = [
            'POST' => [
                'action' => 'Push new node',
                'template' => [],
            ],
            'OPTIONS' => ['action' => 'List available options'],
        ];
        $headers = ['Allow' => implode(', ', array_keys($options))];

        return $this->handleView($this->view($options, 200, $headers));
    }

    /**
     * Handles dictionary listing.
     *
     * @Rest\Get("/profile/dictionary")
     * @Rest\View(statusCode="200")
     */
    public function profileDictionaryAction()
    {
        $document = $this->get('bpi.presentation.document');

        /** @var Audience[] $audiences */
        $audiences = $this->getRepository(Audience::class)->findBy([
            'disabled' => false,
        ]);
        /** @var Category[] $categories */
        $categories = $this->getRepository(Category::class)->findBy([
            'disabled' => false,
         ]);

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
     *
     * TODO: This serves no practical function.
     * @deprecated
     */
    public function profileDictionaryOptionsAction()
    {
        $options = [
            'GET' => [
                'action' => 'Get profile dictionary',
                'output' => [
                    'entities' => [
                        'profile_dictionary',
                    ],
                ],
            ],
            'OPTIONS' => ['action' => 'List available options'],
        ];
        $headers = ['Allow' => implode(', ', array_keys($options))];

        return $this->view($options, 200, $headers);
    }

    /**
     * @Rest\Get("/node/syndicate/{id}")
     * @Rest\View(statusCode="200")
     */
    public function nodeMarkSyndicatedAction(Node $node)
    {
        $agency = $this->getUser();
        if ($node->isOwner($agency)) {
            return $this->createErrorView(
                'Not Acceptable: Trying to syndicate content by owner who already did that',
                406
            );
        }

        $log = new History($node, $agency->getAgencyId()->id(), new \DateTime(), 'syndicate');

        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($log);

        $node->setSyndications(((int)$node->getSyndications()) + 1);
        $dm->persist($node);

        $dm->flush();

        // TODO: Add a meaningful output.
        return [];
    }

    /**
     * Mark node as syndicated.
     *
     * @Rest\Get("/node/syndicated")
     * @Rest\View(statusCode="200")
     *
     * @deprecated
     */
    public function nodeSyndicatedAction(Request $request)
    {
        $nodeId = $request->get('id');

        $result = $this->forward(
            'Bpi\ApiBundle\Controller\RestController::nodeMarkSyndicatedAction',
            [
                'id' => $nodeId,
            ]
        );

        return $result;
    }

    /**
     * Mark node as deleted
     *
     * @Rest\Get("/node/delete")
     * @Rest\View(statusCode="200")
     *
     * TODO: Forward to new method with ParamConverter.
     *
     * @deprecated
     */
    public function nodeDeleteAction(Request $request)
    {
        // @todo Add check if node exists

        $nodeId = $request->get('id');

        $agencyId = $this->getUser()->getAgencyId()->id();

        /** @var \Bpi\ApiBundle\Domain\Repository\NodeRepository $nodeRepository */
        $nodeRepository = $this->getRepository('BpiApiBundle:Aggregate\Node');
        $node = $nodeRepository->delete($nodeId, $agencyId);

        if ($node == null) {
            return new Response('This node does not belong to you', 403);
        }

        // TODO: Add a meaningful output.
        return [];
    }

    /**
     * Node resource.
     *
     * @Rest\Get("/node")
     * @Rest\View()
     */
    public function nodeResourceAction(Request $request)
    {
        // Handle query by node id
        if ($id = $request->get('id')) {
            // TODO: This looks wrong.
            // SDK can not handle properly redirects, so query string is used
            // @see https://github.com/symfony/symfony/issues/7929
            $params = [
                'id' => $id,
                '_authorization' => [
                    'agency' => $this->getUser()->getAgencyId()->id(),
                    'token' => $this->container->get('security.context')->getToken()->token,
                ],
            ];

            return $this->redirect($this->generateUrl('node', $params));
        }

        $document = $this->get('bpi.presentation.document');
        $entity = $document->createRootEntity('node');
        $controls = $document->createHypermediaSection();
        $entity->setHypermedia($controls);
        $controls->addQuery($document->createQuery('search', 'abc', ['id'], 'Find a node by ID'));
        $controls->addQuery($document->createQuery('filter', 'xyz', ['name', 'title'], 'Filtration'));
        $controls->addLink($document->createLink('self', 'Self'));
        $controls->addLink($document->createLink('collection', 'Collection'));

        return $document;
    }
}

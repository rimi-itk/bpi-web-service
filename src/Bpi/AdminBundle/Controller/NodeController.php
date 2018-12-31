<?php

namespace Bpi\AdminBundle\Controller;

use Bpi\ApiBundle\Domain\Aggregate\Node;
use Bpi\ApiBundle\Domain\Entity\Category;
use Bpi\ApiBundle\Domain\Entity\Facet;
use Bpi\ApiBundle\Domain\Form\TagType;
use Bpi\ApiBundle\Domain\Repository\AudienceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/node")
 */
class NodeController extends Controller
{
    /**
     * @return \Bpi\ApiBundle\Domain\Repository\NodeRepository
     */
    private function getRepository()
    {
        return $this->get('doctrine_mongodb')
            ->getRepository(Node::class);
    }

    /**
     * @Route(path="/", name="bpi_admin_node")
     * @Template("BpiAdminBundle:Node:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $param = $request->query->get('sort');
        $direction = $request->query->get('direction');
        $search = $request->query->get('search');
        $query = $this->getRepository()->listAll($param, $direction, $search);

        $knpPaginator = $this->get('knp_paginator');

        $pagination = $knpPaginator->paginate(
            $query,
            $request->query->get('page', 1),
            50,
            [
                'defaultSortFieldName' => 'resource.title',
                'defaultSortDirection' => 'desc',
            ]
        );

        return ['pagination' => $pagination];
    }

    /**
     * @Route(path="/deleted", name="bpi_admin_node_deleted")
     * @Template("BpiAdminBundle:Node:index.html.twig")
     */
    public function deletedAction(Request $request)
    {

        $query = $this->getRepository()->listAll(null, null, null, true);
        $knpPaginator = $this->get('knp_paginator');

        $pagination = $knpPaginator->paginate(
            $query,
            $request->query->get('page', 1),
            50,
            [
                'defaultSortFieldName' => 'resource.title',
                'defaultSortDirection' => 'desc',
            ]
        );

        return [
            'pagination' => $pagination,
            'delete_lable' => 'Undelete',
            'delete_url' => 'bpi_admin_node_restore',
        ];
    }

    /**
     * @Route(path="/edit/{id}", name="bpi_admin_node_edit")
     * @Template("BpiAdminBundle:Node:form.html.twig")
     */
    public function editAction(Request $request, Node $node)
    {
        $form = $this->createNodeForm($node);
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();

        if ($request->isMethod('POST')) {
            $submittedNode = $request->get('form');
            $changeAuthorFirstName = $node->getAuthorFirstName() != $submittedNode['authorFirstName'];
            $changeAuthorLastName = $node->getAuthorLastName() != $submittedNode['authorLastName'];
            $changeAuthor = $changeAuthorFirstName || $changeAuthorLastName;
            $changeCategory = $node->getCategory()->getId() != $submittedNode['category'];
            $changeAudience = $node->getAudience()->getId() != $submittedNode['audience'];

            $submittedTags = [];
            if (!empty($submittedNode['tags']) && is_array($submittedNode['tags'])) {
                foreach ($submittedNode['tags'] as $tag) {
                    $submittedTags[] = $tag['tag'];
                }
            }


            $checks = [
                'author' => [
                    'check' => $changeAuthor,
                    'oldValue' => $node->getAuthor()->getFullName(),
                    'newValue' => ($submittedNode['authorFirstName'] ? $submittedNode['authorFirstName'].' ' : '').$submittedNode['authorLastName'],
                ],
                'category' => array(
                    'check' => $changeCategory,
                    'oldValue' => $node->getCategory()->getCategory(),
                    'newValue' => $dm->getRepository('BpiApiBundle:Entity\Category')->find($submittedNode['category'])->getCategory()
                ),
                'audience' => array(
                    'check' => $changeAudience,
                    'oldValue' => $node->getAudience()->getAudience(),
                    'newValue' => $dm->getRepository('BpiApiBundle:Entity\Audience')->find($submittedNode['audience'])->getAudience()
                ),
            ];

            $changes = [];
            foreach ($checks as $key => $check) {
                if ($check['check']) {
                    $changes[$key] = [
                        'oldValue' => $check['oldValue'],
                        'newValue' => $check['newValue'],
                    ];
                }
            }
            $changes['nodeId'] = $node->getId();
            $changes['tags'] = $submittedTags;

            $form->handleRequest($request);
            if ($form->isValid()) {
                $modifyTime = new \DateTime();
                $node->setMtime($modifyTime);
                /** @var \Bpi\ApiBundle\Domain\Repository\FacetRepository $facetRepository */
                $facetRepository = $this->get('doctrine_mongodb')
                    ->getRepository('BpiApiBundle:Entity\Facet');
                $facetRepository->updateFacet($changes);

                $this->getRepository()->save($node);

                return $this->redirectToRoute('bpi_admin_node');
            }
        }

        $assets = [];
        $nodeAssets = $node->getAssets();
        if (!empty($nodeAssets)) {
            $assets = $this->prepareAssets($nodeAssets->getCollection());
        }

        return [
            'form' => $form->createView(),
            'id' => $node->getId(),
            'assets' => $assets,
        ];
    }

    /**
     * @Route(path="/details/{id}", name="bpi_admin_node_details")
     * @Template("BpiAdminBundle:Node:details.html.twig")
     */
    public function detailsAction(Node $node)
    {
        return [
            'node' => $node,
        ];
    }

    /**
     * @Route(path="/delete/{id}", name="bpi_admin_node_delete")
     */
    public function deleteAction(Node $node)
    {
        $this->getRepository()->delete($node->getId(), 'ADMIN');
        $this->get('doctrine_mongodb')
            ->getRepository(Facet::class)
            ->delete($node->getId());

        return $this->redirectToRoute('bpi_admin_node');
    }

    /**
     * @Route(path="/restore/{id}", name="bpi_admin_node_restore")
     */
    public function restoreAction(Node $node)
    {
        $this->getRepository()->restore($node->getId(), 'ADMIN');

        /** @var \Bpi\ApiBundle\Domain\Repository\FacetRepository $facetRepository */
        $facetRepository = $this->get('doctrine_mongodb')
            ->getRepository(Facet::class);
        $facetRepository->prepareFacet($node);

        return $this->redirectToRoute('bpi_admin_node');
    }

    private function createNodeForm($node, $new = false)
    {
        $formBuilder = $this->createFormBuilder($node, ['csrf_protection' => false])
            ->add(
                'authorFirstName',
                TextType::class,
                [
                    'label' => 'Author first name',
                    'required' => true,
                ]
            )
            ->add(
                'authorLastName',
                TextType::class,
                [
                    'label' => 'Author last name',
                    'required' => false,
                ]
            )
            ->add(
                'authorAgencyId',
                TextType::class,
                [
                    'label' => 'Author agency id',
                    'required' => true,
                    'disabled' => true,
                ]
            )
            ->add(
                'ctime',
                DateType::class,
                [
                    'label' => 'Creation time',
                    'required' => true,
                    'widget' => 'single_text',
                    'disabled' => true,
                ]
            )
            ->add(
                'mtime',
                DateType::class,
                [
                    'label' => 'Modify time',
                    'required' => true,
                    'widget' => 'single_text',
                    'disabled' => true,
                ]
            )
            ->add('title', TextType::class)
            ->add('teaser', TextareaType::class)->setRequired(false)
            ->add('body', TextareaType::class)->setRequired(false)
            ->add(
                'category',
                null,
                array(
                    'choice_label' => 'category',
                )
            )
            ->add(
                'audience',
                null,
                array(
                    'choice_label' => 'audience',
                )
            )
            ->add(
                'tags',
                CollectionType::class,
                [
                    'entry_type' => TagType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                ]
            );

        if (!$new) {
            $formBuilder->add(
                'deleted',
                CheckboxType::class,
                [
                    'required' => false,
                ]
            );
        }

        $formBuilder->add('save', SubmitType::class, ['attr' => ['class' => 'btn']]);

        return $formBuilder->getForm();
    }

    /**
     * Filter assets on images and documents
     *
     * @param $nodeAssets
     *
     * @return array
     */
    protected function prepareAssets($nodeAssets)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $assets = [];
        foreach ($nodeAssets as $asset) {
            if (in_array($asset->getExtension(), $imageExtensions)) {
                $assets['images'][] = $asset;
            } else {
                $assets['documents'][] = $asset;
            }
        }

        return $assets;
    }
}

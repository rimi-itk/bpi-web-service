<?php

namespace Bpi\AdminBundle\Controller;

use Bpi\ApiBundle\Domain\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/category")
 */
class CategoryController extends Controller
{
    /**
     * @return \Bpi\ApiBundle\Domain\Repository\CategoryRepository
     */
    private function getRepository()
    {
        return $this->get('doctrine_mongodb')
            ->getRepository(Category::class);
    }

    /**
     * @Route(path="/", name="bpi_admin_category")
     * @Template("BpiAdminBundle:Category:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $param = $request->query->get('sort');
        $direction = $request->query->get('direction');

        $query = $this->getRepository()->listAll($param, $direction);

        $knpPaginator = $this->get('knp_paginator');

        $pagination = $knpPaginator->paginate(
            $query,
            $request->query->get('page', 1),
            50,
            [
                'defaultSortFieldName' => 'category',
                'defaultSortDirection' => 'asc',
            ]
        );

        return ['pagination' => $pagination];
    }

    /**
     * @Route(path="/new", name="bpi_admin_category_new")
     * @Template("BpiAdminBundle:Category:form.html.twig")
     */
    public function newAction(Request $request)
    {
        $category = new Category();
        $form = $this->createCategoryForm($category);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getRepository()->save($category);

                return $this->redirectToRoute('bpi_admin_category');
            }
        }

        return [
            'form' => $form->createView(),
            'id' => null,
        ];
    }

    /**
     * @Route(path="/edit/{id}", name="bpi_admin_category_edit")
     * @Template("BpiAdminBundle:Category:form.html.twig")
     */
    public function editAction(Request $request, Category $category)
    {
        $form = $this->createCategoryForm($category);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getRepository()->save($category);

                return $this->redirectToRoute('bpi_admin_category');
            }
        }

        return [
            'form' => $form->createView(),
            'id' => $category->getId(),
        ];
    }

    /**
     * @Route(path="/disable/{id}", name="bpi_admin_category_disable")
     */
    public function disableAction(Category $category)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $category->setDisabled(true);

        $dm->flush();

        return $this->redirectToRoute('bpi_admin_category');
    }

    /**
     * @Route(path="/enable/{id}", name="bpi_admin_category_enable")
     */
    public function enableAction(Category $category)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $category->setDisabled(false);

        $dm->flush();

        return $this->redirectToRoute('bpi_admin_category');
    }

    private function createCategoryForm($category)
    {
        $formBuilder = $this->createFormBuilder($category)
            ->add('category', TextType::class)
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn']]);

        return $formBuilder->getForm();
    }
}

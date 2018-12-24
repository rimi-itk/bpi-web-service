<?php

namespace Bpi\AdminBundle\Controller;

use Bpi\ApiBundle\Domain\Entity\Audience;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/audience")
 */
class AudienceController extends Controller
{
    /**
     * @return \Bpi\ApiBundle\Domain\Repository\AudienceRepository
     */
    private function getRepository()
    {
        return $this->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('BpiApiBundle:Entity\Audience');
    }

    /**
     * @Route(path="/", name="bpi_admin_audience")
     * @Template("BpiAdminBundle:Audience:index.html.twig")
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
                'defaultSortFieldName' => 'audience',
                'defaultSortDirection' => 'asc',
            ]
        );

        return ['pagination' => $pagination];
    }

    /**
     * @Route(path="/new", name="bpi_admin_audience_new")
     * @Template("BpiAdminBundle:Audience:form.html.twig")
     */
    public function newAction(Request $request)
    {
        $audience = new Audience();
        $form = $this->createAudienceForm($audience, true);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getRepository()->save($audience);

                return $this->redirect(
                    $this->generateUrl('bpi_admin_audience')
                );
            }
        }

        return [
            'form' => $form->createView(),
            'id' => null,
        ];
    }

    /**
     * @Route(path="/edit/{id}", name="bpi_admin_audience_edit")
     * @Template("BpiAdminBundle:Audience:form.html.twig")
     */
    public function editAction(Request $request, Audience $audience)
    {
        $form = $this->createAudienceForm($audience);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getRepository()->save($audience);

                return $this->redirect(
                    $this->generateUrl('bpi_admin_audience')
                );
            }
        }

        return [
            'form' => $form->createView(),
            'id' => $audience->getId(),
        ];
    }

    private function createAudienceForm($audience)
    {
        $formBuilder = $this->createFormBuilder($audience)
            ->add('audience', TextType::class)
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn']]);

        return $formBuilder->getForm();
    }
}

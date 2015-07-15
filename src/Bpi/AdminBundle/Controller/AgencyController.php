<?php

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AgencyController extends Controller
{
    /**
     * @return \Bpi\ApiBundle\Domain\Repository\AgencyRepository
     */
    private function getRepository()
    {
        return $this->get('doctrine.odm.mongodb.document_manager')
          ->getRepository('BpiApiBundle:Aggregate\Agency');
    }

    /**
     * @Template("BpiAdminBundle:Agency:index.html.twig")
     */
    public function indexAction()
    {
        $repo = $this->getRepository();
        $query = $repo->createQueryBuilder();

        $param = $this->getRequest()->query->get('sort');
        $direction = $this->getRequest()->query->get('direction');
        if ($param && $direction) {
            $query->sort($param, $direction);
        }

        $knpPaginator = $this->get('knp_paginator');

        $pagination = $knpPaginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1),
            50,
            array(
                'defaultSortFieldName' => 'public_id',
                'defaultSortDirection' => 'asc',
            )
        );

        return array('pagination' => $pagination);
    }

    /**
     * Show deleted agencies
     *
     * @Template("BpiAdminBundle:Agency:index.html.twig")
     */
    public function deletedAction()
    {
        $query = $this->getRepository()->listAll(true);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1),
            5
        );

        return array(
            'pagination' => $pagination,
            'delete_lable' => 'Undelete',
            'delete_url' => 'bpi_admin_agency_restore',
            'purge' => 1,
        );
    }

    /**
     * @Template("BpiAdminBundle:Agency:form.html.twig")
     */
    public function newAction()
    {
        $agency = new \Bpi\ApiBundle\Domain\Aggregate\Agency();
        $form = $this->createAgencyForm($agency, true);
        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->getRepository()->save($agency);

                return $this->redirect(
                    $this->generateUrl('bpi_admin_agency')
                );
            }
        }

        return array(
            'form' => $form->createView(),
            'id' => null,
        );
    }

    /**
     * @Template("BpiAdminBundle:Agency:form.html.twig")
     */
    public function editAction($id)
    {
        $agency = $this->getRepository()->find($id);
        $form = $this->createAgencyForm($agency);
        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->getRepository()->save($agency);

                return $this->redirect(
                    $this->generateUrl('bpi_admin_agency')
                );
            }
        }

        return array(
            'form' => $form->createView(),
            'id' => $id,
        );
    }

    /**
     * @Template("BpiAdminBundle:Agency:details.html.twig")
     */
    public function detailsAction($id)
    {
        $agency = $this->getRepository()->find($id);

        return array(
            'agency' => $agency
        );
    }

    public function deleteAction($id)
    {
        $this->getRepository()->delete($id);

        return $this->redirect(
            $this->generateUrl("bpi_admin_agency", array())
        );
    }

    public function purgeAction($id)
    {
        $this->getRepository()->purge($id);

        return $this->redirect(
            $this->generateUrl("bpi_admin_agency_deleted", array())
        );
    }

    public function restoreAction($id)
    {
        $this->getRepository()->restore($id);

        return $this->redirect(
            $this->generateUrl("bpi_admin_agency", array())
        );
    }

    private function createAgencyForm($agency, $new = false)
    {
        $formBuilder = $this->createFormBuilder($agency)
          ->add('publicId', 'text', array('label' => 'Public ID'))
          ->add('name', 'text')
          ->add('moderator', 'text')
          ->add('publicKey', 'text')
          ->add('secret', 'text');

        if (!$new) {
            $formBuilder->add('deleted', 'checkbox', array('required' => false));
        }

        return $formBuilder->getForm();
    }
}

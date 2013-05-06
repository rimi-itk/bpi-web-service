<?php

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AgencyController extends Controller
{
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
        $agencies = $this->getRepository()->listAll();
        return array('agencies' => $agencies);
    }

    /**
     * Show deleted agencies
     *
     * @Template("BpiAdminBundle:Agency:index.html.twig")
     */
    public function deletedAction()
    {
        $agencies = $this->getRepository()->listAll(true);
        return array('agencies' => $agencies);
    }

    /**
     * @Template("BpiAdminBundle:Agency:form.html.twig")
     */
    public function newAction()
    {
        $agency = new \Bpi\ApiBundle\Domain\Aggregate\Agency();
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

    private function createAgencyForm($agency)
    {
        $form = $this->createFormBuilder($agency)
        ->add('publicId', 'text')
        ->add('name', 'text')
        ->add('moderator', 'text')
        ->add('publicKey', 'text')
        ->add('secret', 'text')
        ->add('deleted', 'checkbox', array('required' => false))
        ->getForm();
        return $form;
    }
}

<?php

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NodeController extends Controller
{
    /**
     * @return \Bpi\ApiBundle\Domain\Repository\NodeRepository
     */
    private function getRepository()
    {
        return $this->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('BpiApiBundle:Aggregate\Node');
    }

    /**
     * @Template("BpiAdminBundle:Node:index.html.twig")
     */
    public function indexAction()
    {
        $nodes = $this->getRepository()->listAll();
        return array('nodes' => $nodes);
    }

    /**
     * Show deleted nodes
     *
     * @Template("BpiAdminBundle:Node:index.html.twig")
     */
    public function deletedAction()
    {
        $nodes = $this->getRepository()->listAll(true);
        return array(
            'nodes' => $nodes,
            'delete_lable' => 'Undelete',
            'delete_url' => 'bpi_admin_node_restore',
        );
    }

    /**
     * @Template("BpiAdminBundle:Node:form.html.twig")
     */
    public function newAction()
    {
        $node = new \Bpi\ApiBundle\Domain\Aggregate\Node();
        $form = $this->createNodeForm($node, true);
        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->getRepository()->save($node);
                return $this->redirect(
                    $this->generateUrl('bpi_admin_node')
                );
            }

        }

        return array(
            'form' => $form->createView(),
            'id' => null,
        );
    }

    /**
     * @Template("BpiAdminBundle:Node:form.html.twig")
     */
    public function editAction($id)
    {
        $node = $this->getRepository()->find($id);
        $form = $this->createNodeForm($node);
        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->getRepository()->save($node);
                return $this->redirect(
                    $this->generateUrl('bpi_admin_node')
                );
            }
        }

        return array(
            'form' => $form->createView(),
            'id' => $id,
        );
    }

    /**
     * @Template("BpiAdminBundle:Node:details.html.twig")
     */
    public function detailsAction($id)
    {
        $node = $this->getRepository()->find($id);
        return array(
            'node' => $node,
        );
    }

    public function deleteAction($id)
    {
        $this->getRepository()->delete($id, 'ADMIN');
        return $this->redirect(
            $this->generateUrl("bpi_admin_node", array())
        );
    }

    public function restoreAction($id)
    {
        $this->getRepository()->restore($id, 'ADMIN');
        return $this->redirect(
            $this->generateUrl("bpi_admin_node", array())
        );
    }

    private function createNodeForm($node, $new = false)
    {
        $repo = new \Bpi\ApiBundle\Domain\Repository\CategoryRepository();
        $categories = $repo->findAll();
        $categoryOptions = array();
        foreach ($categories as $category) {
            $name = $category->name();
            $categoryOptions[$name] = $name;
        }

        $repo = new \Bpi\ApiBundle\Domain\Repository\AudienceRepository();
        $audiences = $repo->findAll();
        $audienceOptions = array();
        foreach ($audiences as $audience) {
          $name = $audience->name();
          $audienceOptions[$name] = $name;
        }


        $formBuilder = $this->createFormBuilder($node)
            ->add('title', 'text')
            ->add('teaser', 'textarea')->setRequired(false)
            ->add('category', 'choice', array('choices' => $categoryOptions))
            ->add('audience', 'choice', array('choices' => $audienceOptions));

        if (!$new) {
            $formBuilder->add('deleted', 'checkbox', array('required' => false));
        }

        return $formBuilder->getForm();
    }
}

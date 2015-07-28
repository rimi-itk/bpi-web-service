<?php

namespace Bpi\AdminBundle\Controller;

use Bpi\ApiBundle\Domain\Form\TagType;
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
        $formBuilder = $this->createFormBuilder($node)
            ->add(
                'authorFirstName',
                'text',
                array(
                    'label' => 'Author first name',
                    'required' => true
                )
            )
            ->add(
                'authorLastName',
                'text',
                array(
                    'label' => 'Author last name',
                    'required' => false
                )
            )
            ->add(
                'authorAgencyId',
                'text',
                array(
                    'label' => 'Author agency id',
                    'required' => true
                )
            )
            ->add(
                'ctime',
                'datetime',
                array(
                    'label' => 'Creation time',
                    'required' => true,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'disabled' => true
                )
            )
            ->add(
                'mtime',
                'datetime',
                array(
                    'label' => 'Modify time',
                    'required' => true,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text'
                )
            )
            ->add('title', 'text')
            ->add('teaser', 'textarea')->setRequired(false)
            ->add('body', 'textarea')->setRequired(false)
            ->add(
                'category',
                'document',
                array(
                    'class' => 'BpiApiBundle:Entity\Category',
                    'property' => 'category',
                )
            )
            ->add(
                'audience',
                'document',
                array(
                    'class' => 'BpiApiBundle:Entity\Audience',
                    'property' => 'audience'
                )
            )
            ->add(
                'tags',
                'collection',
                array(
                    'type' => new TagType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'options' => array(
                        'required' => false
                    )
                )
            )
        ;

        if (!$new) {
            $formBuilder->add('deleted', 'checkbox', array('required' => false));
        }

        return $formBuilder->getForm();
    }
}

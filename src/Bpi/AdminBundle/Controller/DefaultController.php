<?php

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @Route(path="/", name="bpi_admin_homepage")
     * @Template("BpiAdminBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
        return [];
    }
}

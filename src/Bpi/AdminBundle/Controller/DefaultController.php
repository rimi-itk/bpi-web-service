<?php

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class DefaultController extends Controller
{
    /**
     * @Template("BpiAdminBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }
}

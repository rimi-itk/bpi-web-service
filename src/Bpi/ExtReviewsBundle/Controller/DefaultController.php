<?php

namespace Bpi\ExtReviewsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Template()
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($name)
    {
        return $this->render('BpiExtReviewsBundle:Default:index.html.twig', array('name' => $name));
    }
}

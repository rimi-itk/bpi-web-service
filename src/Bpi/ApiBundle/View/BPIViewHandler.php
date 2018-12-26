<?php

namespace Bpi\ApiBundle\View;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handle the .bpi file extension
 */
class BPIViewHandler
{
    /**
     * @param ViewHandler $viewHandler
     * @param View $view
     * @param Request $request
     * @param string $format
     *
     * @return Response
     */
    public function handleExtension(ViewHandler $handler, View $view, Request $request, $format)
    {
        if (in_array("application/vnd.bpi.api+xml", $request->getAcceptableContentTypes())) {
            $view->setHeader("Content-Type", "application/vnd.bpi.api+xml");
        }

        return $handler->createResponse($view, $request, "xml");
    }
}

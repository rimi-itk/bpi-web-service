<?php
namespace Bpi\ApiBundle\View;

use FOS\RestBundle\View\View,
    FOS\RestBundle\View\ViewHandler,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

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
        if (in_array("application/vnd.bpi.api+xml", $request->getAcceptableContentTypes()))
            $view->setHeader("Content-Type", "application/vnd.bpi.api+xml");

        return $handler->createResponse($view, $request, "xml");
    }

}

<?php
namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Symfony\Component\Routing;

class FakeRouter implements Routing\RouterInterface
{
    public function generate($name, $parameters = array(), $absolute = false)
    {
        return 'http://bpi-ws.ci.inlead.dk';
    }

    public function match($pathinfo)
    {
    	return array();
    }

    public function getRouteCollection() {}

    public function setContext(Routing\RequestContext $c) {}

    public function getContext() {}
}

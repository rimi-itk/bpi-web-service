<?php
/**
 * @file
 */

namespace Bpi\RestMediaTypeBundle;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as Router;

/**
 * @Serializer\XmlRoot("bpi")
 */
class XmlResponse
{

    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    public $version;

    /**
     * @Serializer\Exclude
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var int
     */
    protected $code;

    /**
     * @var
     */
    protected $message;

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $status
     */
    public function setCode($code)
    {
        $this->code = $code;
    }


    /**
     * @param string $version API version
     */
    public function __construct($version = '0.0.1')
    {
        $this->version = $version;
    }

    /**
     * Inject router dependency
     *
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $name The name of the route
     * @param mixed $parameters An array of parameters
     * @param Boolean $absolute Whether to generate an absolute URL
     *
     * @return string The generated URL
     *
     * @throws RouteNotFoundException if route doesn't exist
     */
    public function generateRoute($name, $parameters = [], $absolute = false)
    {
        return $this->router->generate($name, $parameters, $absolute);
    }
}

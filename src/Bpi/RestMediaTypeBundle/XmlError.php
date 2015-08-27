<?php
/**
 * @file
 */
namespace Bpi\RestMediaTypeBundle;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("error")
 */
class XmlError extends XmlResponse {
    /**
     * @var
     */
    private $error;

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }
}

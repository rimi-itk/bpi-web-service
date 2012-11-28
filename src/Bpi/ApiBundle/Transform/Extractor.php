<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\RestMediaTypeBundle\Document;

class Extractor
{
    /**
     * @var Document
     */
    protected $doc;

    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    public function extract($entity_name)
    {
        $classname = $this->buildClassName($entity_name);
        $extractor = new $classname($this->doc);
        return $extractor->extract();
    }

    protected function buildClassName($name)
    {
        $classname = __NAMESPACE__.'\\Extractor\\';
        foreach (explode('.', $name) as $part)
            $classname .= ucfirst($part).'\\';
        return rtrim($classname, '\\');
    }
}

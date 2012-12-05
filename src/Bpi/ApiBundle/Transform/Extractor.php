<?php
namespace Bpi\ApiBundle\Transform;

use Bpi\RestMediaTypeBundle\Document;

class Extractor
{
    /**
     * @var Document
     */
    protected $doc;

    /**
     * 
     * @param \Bpi\RestMediaTypeBundle\Document $doc
     */
    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    /**
     * Extract entity by its name
     * 
     * @param string $entity_name
     * @return object extracted domain model
     * @throws \RuntimeException
     */
    public function extract($entity_name)
    {
        $classname = $this->buildClassName($entity_name);
        $extractor = new $classname($this->doc);
        
        if (!($extractor instanceof Extractor\IExtractor))
            throw new \RuntimeException('Given entity name ['.$entity_name.'] has no mapped extractor');
        
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

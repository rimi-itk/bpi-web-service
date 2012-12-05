<?php
namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;

/**
 * Extract Resource entity from presentation
 */
class Resource implements IExtractor
{
    /**
     * @var Document
     */
    protected $doc;

    /**
     * 
     * @inheritdoc
     */
    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    /**
     * 
     * @inheritdoc
     * @return Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function extract()
    {
        $entity = $this->doc->getEntity('resource');
        $builder = new ResourceBuilder();
        return $builder
            ->title($entity->property('title')->getValue())
            ->body($entity->property('body')->getValue())
            ->teaser($entity->property('teaser')->getValue())
            ->ctime(new \DateTime($entity->property('ctime')->getValue()))
            ->build()
        ;
    }
}

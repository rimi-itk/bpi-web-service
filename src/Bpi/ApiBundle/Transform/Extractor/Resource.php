<?php
namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\RestMediaTypeBundle\Element\Property;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\Entity\Asset;
use Gaufrette\File;
use Gaufrette\Adapter\InMemory as MemoryAdapter;
use Gaufrette\Filesystem;

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
     * {@inheritdoc}
     */
    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    /**
     * 
     * {@inheritdoc}
     * @return Bpi\ApiBundle\Domain\Entity\Resource
     */
    public function extract()
    {
        $entity = $this->doc->getEntity('resource');
        $builder = new ResourceBuilder();
        
        $entity->walk(function($e) use($builder) {
            $fs = new Filesystem(new MemoryAdapter());
            if ($e->typeOf(Property::TYPE_ASSET))
            {
                $file = new File($e->getName(), $fs);
                $file->setContent($e->getValue());
                $builder->addAsset(new Asset('', $file));
            }
        });
        
        return $builder
            ->title($entity->property('title')->getValue())
            ->body($entity->property('body')->getValue())
            ->teaser($entity->property('teaser')->getValue())
            ->ctime(new \DateTime($entity->property('ctime')->getValue()))
            ->build()
        ;
    }
}

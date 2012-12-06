<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Domain\Entity\Asset;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;
use Gaufrette\Filesystem;

/**
 * Remote resource like article, news item, etc
 */
class Resource implements IPresentable
{
    protected $title;

    protected $body;

    protected $teaser;

    protected $ctime;

    protected $type = 'article';

    protected $assets = array();
    
    protected $embedded_assets = array();

    public function __construct(
        $title,
        $body,
        $teaser,
        \DateTime $ctime,
        array $assets = null
    )
    {
        $this->title = $title;
        $this->body = $body;
        $this->teaser = $teaser;
        $this->ctime = $ctime;
        $this->embedded_assets = $assets;
    }

    /**
     * 
     * @param \Bpi\ApiBundle\Domain\Entity\Asset $asset
     */
    public function addAsset(Asset $asset)
    {
        $this->assets[] = $asset;
    }
    
    /**
     * Allocating embedded assets in body
     * 
     * @return string body
     */
    protected function allocateEmbeddedAssets()
    {
        $dom = new \DOMDocument();        
        $dom->strictErrorChecking = false;
        $dom->recover = true;
        
        libxml_use_internal_errors(true);
        $result = @$dom->loadHTML($this->body);
        libxml_clear_errors();
        
        if (false !== $result)
        {
            foreach ($this->embedded_assets as $asset)
            {
                $asset->allocateInContent($dom);
            }
            return $dom->saveHTML();
        }
        
        return $this->body;
    }

    /**
     * Copy assets into other filesystem in transactional way
     * Common use case is to persists from memory to storage
     * 
     * @param \Gaufrette\Filesystem $fs
     * @return Resource\AssetsTransaction
     */
    public function copyAssets(Filesystem $fs)
    {
        $transaction = new Resource\AssetsTransaction();
        try {
            foreach($this->assets as $asset)
            {
                $transaction->add($asset);
                $asset->copy($fs);
            }
        } catch(\RuntimeException $e)
        {
            $transaction->markAsFailed($e);
        }
        return $transaction;
    }
    
    /**
     * Calculate similarity of resources by checking body contents
     *
     * @param Resource $resource
     * @return boolean
     */
    public function isSimilar(Resource $resource)
    {
        if ($this->body == $resource->body)
            return true;

        similar_text(strip_tags($this->body), strip_tags($resource->body), $similarity);
        if ($similarity > 50)
            return true;

        return false;
    }

    /**
     * @inheritDoc
     */
    public function transform(Document $document)
    {
        try {
            $entity = $document->createEntity('resource');
            $document->currentEntity()->addChildEntity($entity);
        } catch (\RuntimeException $e) {
            $document->appendEntity($entity);
        }

        $body = $this->allocateEmbeddedAssets();
        $body = str_ireplace('__embedded_asset_base_url__', $document->generateRoute("get_asset", array('filename'=>'')), $body);
        
        $entity->addProperty($document->createProperty('title', 'string', $this->title));
        $entity->addProperty($document->createProperty('body', 'string', $body));
        $entity->addProperty($document->createProperty('teaser', 'string', $this->teaser));
        $entity->addProperty($document->createProperty('ctime', 'dateTime', $this->ctime));
        $entity->addProperty($document->createProperty('type', 'string', $this->type));   
    }

    /**
     *
     * @param \Bpi\ApiBundle\Domain\Entity\Resource $resource
     * @param string $field
     * @param int $order 1=asc, -1=desc
     * @return int see strcmp PHP function
     */
    public function compare(Resource $resource, $field, $order = 1)
    {
        if (stristr($field, '.')) {
            list($local_field, $child_field) = explode(".", $field, 2);
            return $this->$local_field->compare($resource->$local_field, $child_field, $order);
        }

        $cmp = new Comparator($this->$field, $resource->$field, $order);
        return $cmp->getResult();
    }
}

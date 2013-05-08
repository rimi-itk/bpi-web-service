<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
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

    protected $copyleft;

    protected $router;
    protected $filesystem;

    /**
     *
     * @param string $title
     * @param string $body
     * @param string $teaser
     * @param Copyleft $copyleft
     * @param \DateTime $ctime
     * @param array $files
     * @param array $assets
     * @param \Gaufrette\Filesystem $filesystem
     * @param object $router
     */
    public function __construct(
        $title,
        $body,
        $teaser,
        Copyleft $copyleft,
        \DateTime $ctime,
        array $files = null,
        array $assets = array(),
        Filesystem $filesystem,
        $router
    )
    {
        $this->title = $title;
        $this->body = new Resource\Body($body, $filesystem, $router);
        $this->body->rebuildInlineAssets();
        $this->teaser = $teaser;
        $this->copyleft = $copyleft;
        $this->ctime = $ctime;
        $this->assets = array_merge($assets, $this->body->getAssets());
        $this->filesystem = $filesystem;
        $this->router = $router;
    }

    /**
     * Copy assets into other filesystem in transactional way
     * Common use case is to persists from memory to storage
     *
     * @param  \Gaufrette\Filesystem      $fs
     * @return Resource\AssetsTransaction
     */
    public function copyAssets(Filesystem $fs)
    {
        $transaction = new Resource\AssetsTransaction();
        try {
            foreach ($this->assets as $asset) {
                $transaction->add($asset);
                $asset->copy($fs);
            }
        } catch (\RuntimeException $e) {
            $transaction->markAsFailed($e);
        }

        return $transaction;
    }

    /**
     * Calculate similarity of resources by checking body contents
     *
     * @param  Resource $resource
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
     * {@inheritdoc}
     */
    public function transform(Document $document)
    {
        try {
            $entity= $document->currentEntity();
        } catch (\RuntimeException $e) {
            $entity = $document->createEntity('entity', 'resource');
            $document->appendEntity($entity);
        }

        // Add assets to presentation.
        $i = 1;
        foreach ($this->assets as $asset) {
            $assetUrl = $document->generateRoute("get_asset", array('filename'=> $asset['file'], 'extension'=> $asset['extension']), true);
            $entity->addProperty($document->createProperty('asset' . $i, 'asset', $assetUrl));
            $i++;
        }

        $copyleft = '<p>' . $this->copyleft . '</p>';

        $entity->addProperty($document->createProperty('title', 'string', $this->title));
        $entity->addProperty($document->createProperty('body', 'string', $this->body->getFlattenContent() . $copyleft));
        $entity->addProperty($document->createProperty('teaser', 'string', $this->teaser));
        $entity->addProperty($document->createProperty('creation', 'dateTime', $this->ctime));
        $entity->addProperty($document->createProperty('type', 'string', $this->type));
    }

    /**
     *
     * @param  \Bpi\ApiBundle\Domain\Entity\Resource $resource
     * @param  string                                $field
     * @param  int                                   $order    1=asc, -1=desc
     * @return int                                   see strcmp PHP function
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

    /**
     * This method will be invoked after the entity has been loaded from doctrine
     */
    public function wakeup()
    {
        $this->body = new Resource\Body($this->body, $this->filesystem, $this->router);
    }

    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function getTeaser()
    {
        return $this->teaser;
    }
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }

    public function addAsset($asset)
    {
        $this->assets[] = $asset;
    }
}

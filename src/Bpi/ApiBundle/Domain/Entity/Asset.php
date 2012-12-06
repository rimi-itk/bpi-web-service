<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Gaufrette\File;
use Gaufrette\Filesystem;

/**
 * Media asset represents images, docs, files
 */
class Asset
{
    protected $id;

    protected $title;

    protected $file;

    /**
     *
     * @param string $title
     * @param type $file
     */
    public function __construct($title, File $file)
    {
        $this->title = $title;
        $this->file = $file;
    }
    
    /**
     * 
     * @param \Gaufrette\Filesystem $fs
     * @throws \RuntimeException when copy process fails
     * @return self
     */
    public function copy(Filesystem $fs)
    {
        $file = $fs->createFile($this->file->getKey());
        $result = $file->setContent($this->file->getContent());
        
        if ($result === false)
            throw new \RuntimeException('Unable to copy file '.$this->file->getName().' into '.get_class($fs->getAdapter()).' filesystem');
        
        return new static($this->file->getKey(), $file);
    }
    
    /**
     * Detach file from filesystem
     */
    public function detach()
    {
        $this->file->delete();
    }
    
    /**
     * Search asset in body and replace src attribute to stub
     * On transformation stage, this stub will be replaced with actual URI
     * 
     * @param \DOMDocument $body
     * @throws \RuntimeException
     */
    public function allocateInContent(\DOMDocument $body)
    {
        $element = $body->getElementById($id = $this->file->getKey());
        if (is_null($element))
            throw new \RuntimeException('Cant find element with id ['.$id.'] in resource body');
        
        $element->setAttribute('src', '__embedded_asset_base_url__/'.$id);
    }
}

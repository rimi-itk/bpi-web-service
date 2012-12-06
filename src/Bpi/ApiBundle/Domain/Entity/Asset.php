<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Gaufrette\File;
use Gaufrette\Filesystem;

/**
 * Media asset represents images, docs, files
 */
class Asset
{
    const EMBEDDED = 'embed';
    const ATTACHED = 'attachment';

    protected $id;

    protected $rel_type;

    protected $file;

    /**
     * @param \Gaufrette\File $file
     * @param string $rel_type relation type: embedded or attached
     */
    public function __construct(File $file, $rel_type)
    {
        $this->rel_type = $rel_type;
        $this->file = $file;
    }

    /**
     *
     * @return boolean
     */
    public function isEmbedded()
    {
        return $this->rel_type == self::EMBEDDED;
    }

    /**
     *
     * @return boolean
     */
    public function isAttached()
    {
        return $this->rel_type == self::ATTACHED;
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

        return new static($file, $this->rel_type);
    }

    /**
     * Detach file from filesystem
     */
    public function detach()
    {
        $this->file->delete();
    }

    public function getId()
    {
        return $this->id;
    }
}

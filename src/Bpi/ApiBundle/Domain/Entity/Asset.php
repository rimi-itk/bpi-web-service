<?php
namespace Bpi\ApiBundle\Domain\Entity;

use Gaufrette\File;

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
}

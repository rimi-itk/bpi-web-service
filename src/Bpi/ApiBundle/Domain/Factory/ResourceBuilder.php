<?php
namespace Bpi\ApiBundle\Domain\Factory;

use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\ValueObject\Copyleft;
use Bpi\ApiBundle\Domain\ValueObject\Material;
use Gaufrette\File;
use Symfony\Component\Routing\RouterInterface;

class ResourceBuilder
{
    protected $type;
    protected $title, $body, $teaser, $ctime, $copyleft;
    protected $files = array();
    protected $filesystem;
    protected $router;
    protected $materials = array();

    protected $category;
    protected $audience;
    protected $url;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     *
     * @param string $type
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @param string $title
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function title($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     *
     * @param string $body
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     *
     * @param string $teaser
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function teaser($teaser)
    {
        $this->teaser = $teaser;
        return $this;
    }

    /**
     *
     * @param string $url
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     *
     * @param \DateTime $dt
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function ctime(\DateTime $dt)
    {
        $this->ctime = $dt;
        return $this;
    }

    /**
     *
     * @param \Gaufrette\File $file
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function addFile(File $file)
    {
        $this->files[] = $file;
        return $this;
    }

    /**
     *
     * @param \Bpi\ApiBundle\Domain\ValueObject\Copyleft $copyleft
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function copyleft(Copyleft $copyleft)
    {
        $this->copyleft = $copyleft;
        return $this;
    }

    public function setAudience($audience)
    {
        $this->audience = $audience;
        return $this;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Add material to resource
     *
     * @param string $material fully qualified number like 100200:1234567
     */
    public function addMaterial($material) {
        $this->materials[] = Material::create($material);
        return $this;
    }

    /**
     *
     * @return boolean
     */
    protected function isValidForBuild()
    {
        return !(is_null($this->type)
            || is_null($this->title)
            || is_null($this->body)
            || is_null($this->teaser)
            || is_null($this->ctime)
            || is_null($this->copyleft)
        );
    }

    /**
     *
     * @return Resource
     * @throws \RuntimeException
     */
    public function build()
    {
        // Copyleft is optional, so null object will be placed
        if (is_null($this->copyleft))
            $this->copyleft = new Copyleft('');

        if (!$this->isValidForBuild()) {
            throw new \RuntimeException('Invalid state: can not build');
        }

        return new Resource(
            $this->type,
            $this->title,
            $this->body,
            $this->teaser,
            $this->copyleft,
            $this->ctime,
            $this->category,
            $this->audience,
            $this->files,
            $this->router,
            $this->materials,
            $this->url
        );
    }
}

<?php
namespace Bpi\ApiBundle\Domain\Factory;

use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Entity\Asset;

class ResourceBuilder
{
    protected $title, $body, $teaser, $ctime;
    protected $assets = array();

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
     * @param \DateTime $dt
     * @return \Bpi\ApiBundle\Domain\Factory\ResourceBuilder
     */
    public function ctime(\DateTime $dt)
    {
        $this->ctime = $dt;
        return $this;
    }

    public function addAsset(Asset $asset)
    {
        $this->assets[] = $asset;
    }
    
    /**
     *
     * @return boolean
     */
    protected function isValidForBuild()
    {
        return !(is_null($this->title)
            || is_null($this->body)
            || is_null($this->teaser)
            || is_null($this->ctime)
        );
    }

    /**
     *
     * @return Resource
     * @throws \RuntimeException
     */
    public function build()
    {
        if (!$this->isValidForBuild())
            throw new \RuntimeException('Invalid state: can not build');

        $resource = new Resource($this->title, $this->body, $this->teaser, $this->ctime, $this->assets);
//        array_walk($this->assets, array($resource, 'addAsset')); // simply calls $resource->addAsset method for each item in array
        return $resource;
    }
}

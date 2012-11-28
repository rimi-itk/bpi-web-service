<?php
namespace Bpi\ApiBundle\Transform;

class Path
{
    protected $path;

    /**
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Transform client path into full qulified domain model structure
     *
     * @return string
     */
    public function toDomain()
    {
        return str_ireplace(array(
            'profile.category',
            'profile.audience',
        ), array(
            'profile.taxonomy.category.name',
            'profile.taxonomy.audience.name',
        ), $this->path);
    }
}

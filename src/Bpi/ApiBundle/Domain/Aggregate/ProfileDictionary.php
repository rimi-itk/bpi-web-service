<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;

class ProfileDictionary implements IPresentable
{
    protected $audiences;
    protected $categories;

    public function __construct($audiences, $categories)
    {
        $this->audiences = $audiences;
        $this->categories = $categories;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Document $document)
    {
        foreach ($this->audiences as $audience)
            $audience->transform($document);

        foreach ($this->categories as $category)
            $category->transform($document);
    }
}

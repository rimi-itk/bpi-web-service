<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;

class ProfileDictionary implements IPresentable
{
    protected $id;
    protected $audiences = array();
    protected $categories = array();

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
        foreach ($this->audiences as $audience) {
            $obj = new Audience($audience);
            $obj->transform($document);
        }

        foreach ($this->categories as $category) {
            $obj = new Category($category);
            $obj->transform($document);
        }
    }
}

<?php
namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\Profile as DomainProfile;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;
use Bpi\ApiBundle\Domain\Factory\ProfileBuilder as Builder;

/**
 * Extract Profile entity from presentation
 */
class Profile implements IExtractor
{
    /**
     * @var Document
     */
    protected $doc;

    /**
     *
     * {@inheritdoc}
     */
    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    /**
     *
     * {@inheritdoc}
     * @return DomainProfile
     */
    public function extract()
    {
        $entity = $this->doc->getEntity('profile');

        $builder = new Builder();
        $builder
            ->audience(new Audience($entity->property('audience')->getValue()))
            ->category(new Category($entity->property('category')->getValue()))
        ;

        // optional yearwheel property
        if ($entity->hasProperty('yearwheel'))
        {
            $builder->yearwheel(new Yearwheel($entity->property('yearwheel')->getValue()));
        }

        // optional tags property
        if ($entity->hasProperty('tags'))
        {
            $builder->tags($entity->property('tags')->getValue());
        }

        return $builder->build();
    }
}

<?php
namespace Bpi\ApiBundle\Transform\Extractor;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\Profile as DomainProfile;
use Bpi\ApiBundle\Domain\Entity\Profile\Taxonomy;
use Bpi\ApiBundle\Domain\ValueObject\Audience;
use Bpi\ApiBundle\Domain\ValueObject\Category;
use Bpi\ApiBundle\Domain\ValueObject\Yearwheel;
use Bpi\ApiBundle\Domain\ValueObject\Tag;
use Bpi\ApiBundle\Domain\Repository\YearwheelRepository;

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
        $profile = new DomainProfile(new Taxonomy(
            new Audience($entity->property('audience')->getValue()),
            new Category($entity->property('category')->getValue())
        ));

        // optional yearwheel property
        if ($entity->hasProperty('yearwheel'))
        {
            $yearwheel = new Yearwheel($entity->property('yearwheel')->getValue());
            $repo = new YearwheelRepository();
            if (!$repo->contains($yearwheel))
            {
                throw new Exception('Incorrect yearwheel value');
            }
            $profile->setYearwheel($yearwheel);
        }

        // optional tags property
        if ($entity->hasProperty('tags'))
        {
            $tags = explode(",", $entity->property('yearwheel')->getValue());
            if (count($tags))
            {
                $tags = array_unique($tags);
                array_walk($tags, function(&$e){
                    $e = new Tag(trim($e));
                });

                $profile->setTags($tags);
            }
        }

        return $profile;
    }
}

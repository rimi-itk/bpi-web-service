<?php
namespace Bpi\ApiBundle\Transform\Extractor\Agency;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\Author as DomainAuthor;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Transform\Extractor\IExtractor;

/**
 * Extract Author entity from presentation
 */
class Author implements IExtractor
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
     * @return \Bpi\ApiBundle\Domain\Entity\Author
     */
    public function extract()
    {
        $agency = $this->doc->getEntity('agency');
        $author = $agency->getChildEntity('author');

        $firstname = $author->hasProperty('firstname') ? $author->property('firstname')->getValue() : null;
        $lastname = $author->hasProperty('lastname') ? $author->property('lastname')->getValue() : null;
        if (is_null($lastname) && !is_null($firstname)) {
            $lastname = $firstname;
            unset($firstname);
        }

        return new DomainAuthor(
            new AgencyId($agency->property('id')->getValue()),
            $author->property('id')->getValue(),
            $lastname,
            $firstname
        );
    }
}

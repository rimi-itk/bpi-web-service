<?php

namespace Bpi\ApiBundle\Transform\Extractor;

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
        $author = $this->doc->getEntity('author');

        $firstname = $author->hasProperty('firstname') ? $author->property('firstname')->getValue() : null;
        $lastname = $author->hasProperty('lastname') ? $author->property('lastname')->getValue() : null;
        if (is_null($lastname) && !is_null($firstname)) {
            $lastname = $firstname;
            unset($firstname);
        }

        return new DomainAuthor(
            new AgencyId($author->property('agency_id')->getValue()),
            $author->property('local_id')->getValue(),
            $lastname,
            $firstname
        );
    }
}

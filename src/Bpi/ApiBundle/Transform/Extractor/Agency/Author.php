<?php
namespace Bpi\ApiBundle\Transform\Extractor\Agency;

use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\Entity\Author as DomainAuthor;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\ApiBundle\Transform\Extractor\IExtractor;

class Author implements IExtractor
{
    /**
     * @var Document
     */
    protected $doc;

    public function __construct(Document $doc)
    {
        $this->doc = $doc;
    }

    public function extract()
    {
        $agency = $this->doc->getEntity('agency');
        $author = $agency->getChildEntity('author');

        $firstname = $author->hasProperty('firstname') ? $author->property('firstname') : null;
        $lastname = $author->hasProperty('lastname') ? $author->property('lastname') : null;
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

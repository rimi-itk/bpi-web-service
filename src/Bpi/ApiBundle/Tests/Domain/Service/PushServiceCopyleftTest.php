<?php
namespace Bpi\ApiBundle\Tests\Domain\Service;

use Bpi\ApiBundle\Domain\Service\PushService;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Factory\ResourceBuilder;
use Bpi\ApiBundle\Domain\ValueObject\AgencyId;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Domain\ValueObject\Param\Authorship;

class PushServiceCopyleftTest extends \PHPUnit_Framework_TestCase
{
    protected $service;
    protected $author;
    protected $profile;

    const AGENCY_NAME = '_test_agency_';

    public function __construct()
    {
        // stubs chain should return this agency
        $agency = new \Bpi\ApiBundle\Domain\Aggregate\Agency('200100', self::AGENCY_NAME, 'moderator', 'public_key', 'secret');

        $repository = $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->once())
           ->method('findOneBy')
           ->will($this->returnValue($agency))
        ;

        $om = $this->getMock('\Doctrine\Common\Persistence\ObjectManager');
        $om->expects($this->once())
           ->method('getRepository')
           ->will($this->returnValue($repository))
        ;

        $fsm = $this->getMockBuilder('\Knp\Bundle\GaufretteBundle\FilesystemMap')
            ->setConstructorArgs(array(array()))
            ->getMock()
        ;

        $this->service = new PushService($om, $fsm);
        $this->author = new Author(new AgencyId(1), 1, 'Bush', 'George');

        $resource_builder = new ResourceBuilder;
        $this->resource = $resource_builder
            ->body('bravo_body')
            ->teaser('bravo_teaser')
            ->title('bravo_title')
            ->ctime(new \DateTime())
        ;
    }

    /**
     * When pushing an article, the agencyName should be added to the end of the article
     */
    public function testCopyleft()
    {
        $this->service->assignCopyleft($this->author, $this->resource, new Authorship(0));

        // use black box approach by testing transformation result
        $doc = new Document();
        $this->resource->build()->transform($doc);

        $body = $doc->getEntity('resource')->property('body')->getValue();
        $this->assertEquals(
            1,
            preg_match('~' . self::AGENCY_NAME . '?\.$~', strip_tags($body)),
            'Agency name doesn\'t exists in article copyleft'
        );
    }

    /**
     * Does this author wish to add himself as the original author at the end of the body?
     */
    public function testCopyleftWhenAuthorshipOptionEnabled()
    {
        $this->service->assignCopyleft($this->author, $this->resource, new Authorship(1));

        // use black box approach by testing transformation result
        $doc = new Document();
        $this->resource->build()->transform($doc);

        $body = $doc->getEntity('resource')->property('body')->getValue();
        
        $this->assertEquals(
            1,
            preg_match('~' . $this->author->getFullName() . ', ' . self::AGENCY_NAME . '?\.$~', strip_tags($body)),
            'Author doesn\'t exists in article copyleft'
        );
    }
}

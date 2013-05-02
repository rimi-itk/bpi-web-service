<?php
namespace Bpi\ApiBundle\Domain\Aggregate;

use Bpi\ApiBundle\Domain\Entity\Profile;
use Bpi\ApiBundle\Domain\Entity\Resource;
use Bpi\ApiBundle\Domain\Entity\Author;
use Bpi\ApiBundle\Domain\Aggregate\Params;
use Bpi\ApiBundle\Domain\ValueObject\Param\Editable;
use Bpi\ApiBundle\Transform\IPresentable;
use Bpi\RestMediaTypeBundle\Document;
use Bpi\ApiBundle\Transform\Comparator;
use Gaufrette\File;

class History implements IPresentable
{
  protected $node;
  protected $ctime;
  protected $agency;

  public function transform(Document $document)
  {
 /*   $entity = $document->createEntity('node');

    $entity->addProperty($document->createProperty(
            'ctime',
            'dateTime',
            $this->ctime
        ));

    $entity->addProperty($document->createProperty(
            'mtime',
            'dateTime',
            $this->ctime
        ));

    $entity->addProperty($document->createProperty(
            'id',
            'string',
            $this->getId()
        ));

    $entity->addProperty($document->createProperty(
            'editable',
            'boolean',
            $this->params->filter(function($e){ if ($e instanceof Editable) return true; })->first()->isPositive()
        ));

    $document->appendEntity($entity);

    $this->profile->transform($document);
    $document->setCursorOnEntity($entity);
    $this->resource->transform($document);
*/
  }
}

<?php

namespace Bpi\ApiBundle\Domain\Repository;

use Bpi\ApiBundle\Domain\Entity\Facet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * FacetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FacetRepository extends DocumentRepository
{
    public function prepareFacet($node) {
        $facet = new Facet();

        $agencyId = $node
            ->getAuthor()
            ->getAgencyId()
        ;

        $agency = $this
            ->dm
            ->getRepository('BpiApiBundle:Aggregate\Agency')
            ->findOneBy(array('public_id' => $agencyId->id()))
            ->getName()
        ;

        $author = $node
            ->getAuthor()
            ->getFullname()
        ;

        $categoryId = $node
            ->getCategory()
            ->getId()
        ;
        $category = $this
            ->dm
            ->getRepository('BpiApiBundle:Entity\Category')
            ->findOneBy(array('_id' => $categoryId))
            ->getCategory()
        ;

        $audienceId = $node
            ->getAudience()
            ->getId()
        ;
        $audience = $this
            ->dm
            ->getRepository('BpiApiBundle:Entity\Audience')
            ->findOneBy(array('_id' => $audienceId))
            ->getAudience()
        ;

        $facets = array(
            'Agency' => array($agency),
            'Author' => array($author),
            'Category' => array($category),
            'Audience' => array($audience),
            'Tags' => '',
            'Content type' => '',
        );

        $setFacets = new \stdClass();
        array_walk($facets, function ($facet, $key) use (&$setFacets) {
            if (!empty($facet)) {
                $setFacets->$key = $facet[0];
            }
        });

        $facet->setNodeId($node->getId());
        $facet->setFacetData($setFacets);

        $this->dm->persist($facet);
        $this->dm->flush();
    }

    public function getNodesByFilter($facets = array())
    {

    }
}
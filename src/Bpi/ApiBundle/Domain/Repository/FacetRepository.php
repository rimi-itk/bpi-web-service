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
    /**
     * @param $node
     */
    public function prepareFacet($node) {
        $facet = new Facet();

        $agencyId = $node
            ->getAuthor()
            ->getAgencyId()
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

        $tags = array();
        $nodeTags = $node->getTags();
        foreach ($nodeTags as $key => $tag) {
            $tags[] = $tag->getTag();
        }


        $facets = array(
            'agency_id' => array($agencyId->id()),
            'category' => array($category),
            'audience' => array($audience),
            'tags' => array($tags),
            'contentType' => '',
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

    /**
     * @param array $filters
     * @return array
     */
    public function getFacetsByRequest($filters = array())
    {
        $facets = array();
        $qb = $this->createQueryBuilder('Entity\Facet')
            ->map('
                function() {
                    for (var i in this.facetData) {
                        if (i == "tags") {
                            for (var j in this.facetData[i]) {
                                var key = {
                                    facetName: "tags",
                                    facetValue: this.facetData[i][j]
                                }
                                emit(key, 1);
                            }
                        } else {
                            var key = {
                                facetName: i,
                                facetValue: this.facetData[i]
                            }
                            emit(key, 1);
                        }
                    }
                }
            ')
            ->reduce('
                function(key, values) {
                    var sum = 0;
                    for(var i in values) {
                        sum += values[i];
                    }
                    return sum;
                };
            ')
        ;

        if (empty($filters)) {
            $result = $qb
                ->getQuery()
                ->execute()
            ;
        } else {
            foreach ($filters as $filter_name => $values) {
                $terms = array();
                foreach ($values as $value) {
                    switch ($filter_name) {
                        case 'category' :
                            $terms[] = $value->getCategory();
                            break;

                        case 'audience' :
                            $terms[] = $value->getAudience();
                            break;

                        // Agency, tags
                        default :
                            $terms[] = $value;
                            break;
                    }
                }

                $qb->addOr($qb->expr()->field('facetData.' . $filter_name)->in($terms));
            }

            $result = $qb
                ->getQuery()
                ->execute()
            ;
        }

        foreach ($result as $facet) {
            if ($facet['_id']['facetName'] == 'agency_id') {
                $agency = $this->dm->getRepository('BpiApiBundle:Aggregate\Agency')->loadUserByUsername($facet['_id']['facetValue']);
                $facets['agency_id'][$facet['_id']['facetValue']]['agencyName'] = $agency->getName();
                $facets['agency_id'][$facet['_id']['facetValue']]['count'] = $facet['value'];
            } else {
                $facets[$facet['_id']['facetName']][$facet['_id']['facetValue']] = $facet['value'];
            }
        }

        return $facets;
    }
}
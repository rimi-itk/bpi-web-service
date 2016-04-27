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
     * @var array of applied filters
     */
    private $filters;

    /**
     * @var string applied logical operator
     */
    private $logicalOperator;

    /**
     * Prepare facets for each pushed node
     *
     * @param $node
     */
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

        $author = $node->getAuthor()->getFullName();

        $tags = array();
        $nodeTags = $node->getTags();
        foreach ($nodeTags as $key => $tag) {
            $tags[] = $tag->getTag();
        }


        $facets = array(
            'author' => array($author),
            'agency_id' => array($agencyId->id()),
            'agency_internal' => array($agency->getInternal()),
            'category' => array($category),
            'audience' => array($audience),
            'tags' => array($tags),
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
     * Build facets and get node ids by which will be made request to DB
     *
     * @param array $filters applied filters
     * @param string $logicalOperator by default OR could be AND
     * @return \StdClass
     *  contain array of built facets and array of node ids
     */
    public function getFacetsByRequest($filters = array(), $logicalOperator = 'OR')
    {
        $facets = array();
        $nodeIds = array();

        $this->filters = $filters;
        $this->logicalOperator = $logicalOperator;

        // Ignore deleted nodes.
        $qb = $this->createQueryBuilder('Entity\Facet');

        $filteredNodes = $this->iterateTerms($qb);
        foreach ($filteredNodes as $key => $node) {
            $nid = $node->getNodeId();
            if (isset($nid) && !empty($nid)) {
                $nodeIds[] = $nid;
            }
        }

        $qb->map('
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
                        } else if (i == "channels") {
                            for (var j in this.facetData[i]) {
                                var key = {
                                    facetName: "channels",
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


        $result = $this->iterateTerms($qb);
        foreach ($result as $facet) {
            if ($facet['_id']['facetName'] == 'agency_id') {
                $agency = $this->dm->getRepository('BpiApiBundle:Aggregate\Agency')->loadUserByUsername($facet['_id']['facetValue']);
                $facets['agency_id'][$facet['_id']['facetValue']]['agencyName'] = $agency->getName();
                $facets['agency_id'][$facet['_id']['facetValue']]['count'] = $facet['value'];
            } else {
                $facets[$facet['_id']['facetName']][$facet['_id']['facetValue']] = $facet['value'];
            }
        }

        $nodeFacets = new \StdClass();
        $nodeFacets->facets = $facets;
        $nodeFacets->nodeIds = $nodeIds;

        return $nodeFacets;
    }

    /**
     * Iterate over applied filters and make request to db
     * @param $qb object of query builder
     * @return mixed
     *  array of facets or facet entities
     */
    private function iterateTerms($qb)
    {
        if (!empty($this->filters)) {
            foreach ($this->filters as $filter_name => $values) {
                $terms = array();
                foreach ($values as $value) {
                    switch ($filter_name) {
                        case 'category' :
                            if (is_string($value)) {
                                $terms[] = $value;
                            } else {
                                $terms[] = $value->getCategory();
                            }
                            break;

                        case 'audience':
                            if (is_string($value)) {
                                $terms[] = $value;
                            } else {
                                $terms[] = $value->getAudience();
                            }
                            break;

                        case 'agency_internal':
                            $terms[] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                            break;

                        // Agency, tags
                        default :
                            $terms[] = $value;
                            break;
                    }
                }

                switch (strtoupper($this->logicalOperator)) {
                    case 'OR' :
                        $qb->addOr($qb->expr()->field('facetData.' . $filter_name)->in($terms));
                        break;

                    case 'AND' :
                        $qb->addAnd($qb->expr()->field('facetData.' . $filter_name)->in($terms));
                        break;

                    default :
                        $qb->addOr($qb->expr()->field('facetData.' . $filter_name)->in($terms));
                        break;
                }
            }
        }

        $result = $qb
            ->getQuery()
            ->execute()
        ;

        return $result;
    }

    /**
     * Update facets after agency changes.
     *
     * @param $changes
     * @return bool
     */
    public function updateFacet($changes)
    {
        $qb = $this->dm->createQueryBuilder('BpiApiBundle:Entity\Facet')
            ->update()
            ->multiple(true)
        ;

        if (isset($changes['nodeId'])) {
            $qb->field('nodeId')->equals($changes['nodeId']);
        }

        foreach ($changes as $changedValue => $changed) {
            if (is_array($changed) && isset($changed['newValue']) && isset($changed['oldValue'])) {
                $qb->field('facetData.' . $changedValue)->set($changed['newValue']);
                $qb->field('facetData.' . $changedValue)->equals($changed['oldValue']);
            } elseif ('tags' === $changedValue) {
                $qb->field('facetData.' . $changedValue)->set($changed);
            }

            if ('agency_id' === $changedValue && is_string($changed)) {
                $qb->field('facetData.' . $changedValue)->equals($changed);
            } else {
                $qb->field('facetData.' . $changedValue)->set($changed['newValue']);
                $qb->field('facetData.' . $changedValue)->equals($changed['oldValue']);
            }
        }

        $qb
            ->getQuery()
            ->execute()
        ;

        return true;
    }

    /**
     * Add channel name to facet for all nodes added to channel.
     *
     * @param $channelId
     * @param $nodeIds
     */
    public function addChannelToFacet($channelId, $nodeIds)
    {
        $nids = array();
        foreach ($nodeIds as $id) {
            $nids[] = $id['nodeId'];
        }

        $qb = $this->createQueryBuilder();
        $qb
            ->update()
            ->multiple(true)
            ->field('facetData.channels')->addToSet($channelId)
            ->field('nodeId')->in($nids)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Remove channel name from facet on removing nodes from channel.
     *
     * @param $channelId
     * @param $nodeIds
     */
    public function removeChannelFromFacet($channelId, $nodeIds)
    {
        $nids = array();
        foreach ($nodeIds as $id) {
            $nids[] = $id['nodeId'];
        }

        $qb = $this->createQueryBuilder();
        $qb
            ->update()
            ->multiple(true)
            ->field('facetData.channels')->pull($channelId)
            ->field('nodeId')->in($nids)
            ->getQuery()
            ->execute()
        ;
    }

  /**
   * Remove facet by nodeId.
   *
   * @param $nodeId
   */
    public function delete($nodeId) {
        $this->createQueryBuilder('Facet')
            ->remove()
            ->field('nodeId')->equals($nodeId)
            ->getQuery()
            ->execute()
        ;
    }
}

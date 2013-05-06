<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Gedmo\Tree\Document\MongoDB\Repository\MaterializedPathRepository as DocumentRepository;
use Bpi\ApiBundle\Domain\Entity\NodeQuery;

class NodeRepository extends DocumentRepository
{
    public function findByNodesQuery(NodeQuery $query)
    {
        return $query->executeByDoctrineQuery(
            $this->dm->createQueryBuilder($this->getClassName())
        );
    }

    public function getNode($id)
    {
        return $this->findOneBy(array('id' => $id, 'deleted' => false));
    }

    public function delete($id, $agencyId)
    {
        // @todo Check if node was not deleted before.
        $node = $this->find($id);

        if ($node->getAgencyId() == $agencyId)
        {
            $node->setDeleted();
            $this->dm->persist($node);
            $this->dm->flush($node);
            return $node;
        }

        return null;
    }
}

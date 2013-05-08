<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AgencyRepository extends DocumentRepository implements UserProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @param string $agencyId find agency by public id
     */
    public function loadUserByUsername($agencyId)
    {
        return $this->findOneBy(array('public_id' => $agencyId));
    }

    public function refreshUser(UserInterface $user)
    {

    }

    public function supportsClass($class)
    {
        // @todo Add a proper check?
    }

    /**
     * Show all agencies filtered by "deleted" value.
     *
     * @param bool $deleted
     * @return array
     */
    public function listAll($deleted = false)
    {
        return $this->findBy(array('deleted'=>$deleted), array('public_id'=>0));
    }

    /**
     * Delete an agency
     *
     * @param string $id Agency ID
     */
    public function delete($id)
    {
        $agency = $this->find($id);
        $agency->setDeleted();
        $this->dm->persist($agency);
        $this->dm->flush($agency);
    }

    /**
     * Restore deleted agency
     *
     * @param string $id AgencyID
     */
    public function restore($id)
    {
        $agency = $this->find($id);
        $agency->setDeleted(false);
        $this->dm->persist($agency);
        $this->dm->flush($agency);
    }

    public function save($agency)
    {
        $this->dm->persist($agency);
        $this->dm->flush($agency);
    }
}

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
        ;
    }
}

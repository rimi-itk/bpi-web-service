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
     * @param string $publickey find agency by username=public key
     */
    public function loadUserByUsername($publickey)
    {
        return $this->findOneBy(array('publickey' => $publickey));
    }

    public function refreshUser(UserInterface $user)
    {

    }

    public function supportsClass($class)
    {
        ;
    }
}

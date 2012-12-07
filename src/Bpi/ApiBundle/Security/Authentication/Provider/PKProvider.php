<?php
namespace Bpi\ApiBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Bpi\ApiBundle\Security\Authentication\Token\PKUserToken;

class PKProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

//        if ($user && $this->validate($token->token)) {
//            return $token;
//        }

        return $token;

        throw new AuthenticationException('The PK authentication failed.');
    }

    protected function validate($pkey, $token)
    {
        /** @todo implementation **/
        return true;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof PKUserToken;
    }
}

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

        if (empty($user) || $user->checkToken($token->token) === false) {
            throw new AuthenticationException('Token authentication failed.');
        }

        $token->setAuthenticated(true);
        $token->setUser($user);
        return $token;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof PKUserToken;
    }
}

<?php

namespace Bpi\ApiBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Public key user token
 */
class PKUserToken extends AbstractToken
{
    public $token;

    public function __construct(array $roles = [])
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
//        $this->setAuthenticated(true);
    }

    public function getCredentials()
    {
        return '';
    }
}

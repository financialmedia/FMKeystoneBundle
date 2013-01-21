<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class TokenToken extends PreAuthenticatedToken
{
    protected $token;

    /**
     * Constructor.
     */
    public function __construct($token, $providerKey, array $roles = array())
    {
        parent::__construct($token, $token, $providerKey, $roles);

        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return (string) $this->token;
    }

}
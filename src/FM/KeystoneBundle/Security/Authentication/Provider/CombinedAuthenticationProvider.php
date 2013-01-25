<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Security\Authentication\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CombinedAuthenticationProvider implements AuthenticationProviderInterface
{
    protected $container;
    protected $providers = array();

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addProvider($providerId)
    {
        $this->providers[] = $providerId;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        foreach ($this->providers as $providerId) {
            $provider = $this->container->get($providerId);
            if (true === $provider->supports($token)) {
                return $provider->authenticate($token);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        foreach ($this->providers as $providerId) {
            $provider = $this->container->get($providerId);
            if (true === $provider->supports($token)) {
                return true;
            }
        }

        return false;
    }
}

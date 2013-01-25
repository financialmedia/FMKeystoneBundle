<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Sets the configurable UserProvider for the authentication providers.
 *
 * @see FM\KeystoneBundle\DependencyInjection\Configuration::getConfigTreeBuilder() -> user_provider_service
 *
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 */
class SetUserProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $userProviderServiceId = $container->getParameter('fm_keystone.security.user_provider.id');

        $container->setAlias('fm_keystone.security.user_provider', $userProviderServiceId);
    }
}

<?php

namespace FM\KeystoneBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddEncoderSecretPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fm_keystone.security.encoder.token')) {
            return;
        }

        $container->getDefinition('fm_keystone.security.encoder.token')->replaceArgument(0, $container->getParameter('secret'));
    }
}

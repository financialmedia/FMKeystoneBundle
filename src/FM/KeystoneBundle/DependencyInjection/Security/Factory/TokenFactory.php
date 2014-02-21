<?php

namespace FM\KeystoneBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class TokenFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'fm_keystone.security.authentication.provider.token.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('fm_keystone.security.authentication.provider.token'))
            ->replaceArgument(3, $id)
        ;

        $listenerId = 'fm_keystone.security.authentication.listener.httppost.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('fm_keystone.security.authentication.listener.tokenheader'));
        $listener->replaceArgument(2, $id);

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'http';
    }

    public function getKey()
    {
        return 'keystone_token';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}

<?php

namespace FM\KeystoneBundle\DependencyInjection\Security\Factory;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class HttpPostFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'fm_keystone.security.authentication.provider.combined.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('fm_keystone.security.authentication.provider.combined'))
            ->addMethodCall('addProvider', array($this->createUserProvider($container, $id)))
            ->addMethodCall('addProvider', array($this->createTokenProvider($container, $id)))
        ;

        $listenerId = 'fm_keystone.security.authentication.listener.httppost.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('fm_keystone.security.authentication.listener.httppost'));
        $listener->replaceArgument(2, $id);

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    protected function createUserProvider(ContainerBuilder $container, $id)
    {
        $providerId = 'fm_keystone.security.authentication.provider.user.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('fm_keystone.security.authentication.provider.user'))
            ->replaceArgument(2, $id)
        ;

        return $providerId;
    }

    protected function createTokenProvider(ContainerBuilder $container, $id)
    {
        $providerId = 'fm_keystone.security.authentication.provider.token.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('fm_keystone.security.authentication.provider.token'))
            ->replaceArgument(3, $id)
        ;

        return $providerId;
    }

    public function getPosition()
    {
        return 'http';
    }

    public function getKey()
    {
        return 'keystone_user';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}

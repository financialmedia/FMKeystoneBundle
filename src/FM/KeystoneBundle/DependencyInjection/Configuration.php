<?php

namespace FM\KeystoneBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fm_keystone');

        $rootNode
            ->children()
                ->scalarNode('user_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('token_class')->isRequired()->cannotBeEmpty()->end()

                /**
                 * Use this if your User class doesn't have an username field, but for example an email field.
                 * You can create a class thats implements UserProviderInterface and configure it here.
                 * Defaults to the default UserProvider provided by this bundle.
                 */
                ->scalarNode('user_provider_id')->defaultValue('fm_keystone.security.user_provider')->cannotBeEmpty()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

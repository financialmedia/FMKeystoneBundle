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
                ->scalarNode('user_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                /**
                 * Use this if your User class doesn't have an username field, but for example an email field.
                 * You can create a class thats implements UserProviderInterface and configure it here.
                 * Defaults to the default UserProvider provided by this bundle.
                 */
                ->scalarNode('user_provider_id')
                    ->defaultValue('fm_keystone.user_manager')
                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('service_types')
                    ->prototype('scalar')->end()
                    ->defaultValue(array('compute', 'object-store'))
                ->end()

                ->arrayNode('services')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->isRequired()->end()
                            ->arrayNode('endpoint')
                                // single endpoint, supplied as a string
                                ->beforeNormalization()
                                    ->ifString()->then(function($v) { return array($v); })
                                ->end()
                                // single endpoint, supplied as an array
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return is_array($v) && is_string(key($v)); })
                                    ->then(function($v) { return array($v); })
                                ->end()
                                ->prototype('array')
                                    ->beforeNormalization()
                                        ->ifTrue(function($v) { return is_string($v); })
                                        ->then(function($v) { return array('public_url' => $v, 'admin_url' => $v); })
                                    ->end()
                                    ->children()
                                        ->scalarNode('public_url')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function($v) { return !filter_var($v, FILTER_VALIDATE_URL); })
                                                ->thenInvalid('Invalid url: %s')
                                            ->end()
                                        ->end()
                                        ->scalarNode('admin_url')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function($v) { return !filter_var($v, FILTER_VALIDATE_URL); })
                                                ->thenInvalid('Invalid url: %s')
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

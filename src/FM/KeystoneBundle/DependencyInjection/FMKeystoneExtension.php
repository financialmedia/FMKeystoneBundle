<?php

namespace FM\KeystoneBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FMKeystoneExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('security.yml');

        $container->setParameter('fm_keystone.model.user.class', $config['user_class']);
        $container->setParameter('fm_keystone.security.user_provider.id', $config['user_provider_id']);
        $container->setParameter('fm_keystone.service_types', $config['service_types']);

        $this->loadServices($container, $config['services'], $config['service_types']);
    }

    protected function loadServices(ContainerBuilder $container, array $services, array $types)
    {
        $manager = $container->getDefinition('fm_keystone.service_manager');
        $manager->addMethodCall('setTypes', array($types));

        foreach ($services as $name => $serviceConfig) {
            if (!in_array($serviceConfig['type'], $types)) {
                throw new \LogicException(
                    sprintf(
                        'Service must be one of the registered types (%s), "%s" given',
                        implode(', ', $types),
                        $serviceConfig['type']
                    )
                );
            }

            $id = sprintf('fm_keystone.service.%s', $name);
            $service = $container->setDefinition($id, new DefinitionDecorator('fm_keystone.service'));
            $service->replaceArgument(0, $name);
            $service->replaceArgument(1, $serviceConfig['type']);

            foreach ($serviceConfig['endpoint'] as $endpointConfig) {
                $service->addMethodCall('addEndpoint', array($endpointConfig['public_url'], $endpointConfig['admin_url']));
            }

            $manager->addMethodCall('addService', array(new Reference($id)));
        }
    }
}

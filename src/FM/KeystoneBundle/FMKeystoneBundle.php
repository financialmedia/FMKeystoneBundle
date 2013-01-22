<?php

namespace FM\KeystoneBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use FM\KeystoneBundle\DependencyInjection\Compiler\SetUserProviderPass;
use FM\KeystoneBundle\DependencyInjection\Compiler\AddEncoderSecretPass;
use FM\KeystoneBundle\DependencyInjection\Security\Factory\TokenFactory;
use FM\KeystoneBundle\DependencyInjection\Security\Factory\HttpPostFactory;

class FMKeystoneBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new HttpPostFactory());
        $extension->addSecurityListenerFactory(new TokenFactory());

        $container->addCompilerPass(new AddEncoderSecretPass());
        $container->addCompilerPass(new SetUserProviderPass());
    }
}

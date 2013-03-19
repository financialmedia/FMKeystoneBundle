<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractCommand extends ContainerAwareCommand
{
    protected function getUserProvider()
    {
        $userProviderServiceId = $this->getContainer()->getParameter('fm_keystone.security.user_provider.id');

        return $this->getContainer()->get($userProviderServiceId);
    }

    protected function loadUserByUsername($username)
    {
        return $this->getUserProvider()->loadUserByUsername($username);
    }

    protected function getServiceManager()
    {
        return $this->getContainer()->get('fm_keystone.service_manager');
    }
}

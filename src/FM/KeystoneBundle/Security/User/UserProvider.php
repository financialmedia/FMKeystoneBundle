<?php

namespace FM\KeystoneBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

use FM\KeystoneBundle\Entity\UserManager;

class UserProvider extends UserManager
{
}

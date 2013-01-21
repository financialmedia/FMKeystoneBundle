<?php

namespace FM\KeystoneBundle\Util;

use FM\KeystoneBundle\Entity\UserManager;

class UserManipulator
{
    /**
     * User manager
     *
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Creates a user and returns it.
     *
     * @param string  $username
     * @param string  $password
     * @param string  $email
     * @param boolean $active
     *
     * @return FM\KeystoneBundle\Model\User
     */
    public function create($username, $password, $email, $active)
    {
        $user = $this->userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((boolean) $active);
        $this->userManager->updateUser($user);

        return $user;
    }

    /**
     * Activates the given user.
     *
     * @param string $username
     */
    public function activate($username)
    {
        if (!$user = $this->userManager->findUserByUsername($username)) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        $user->setEnabled(true);
        $this->userManager->updateUser($user);
    }

    /**
     * Deactivates the given user.
     *
     * @param string $username
     */
    public function deactivate($username)
    {
        if (!$user = $this->userManager->findUserByUsername($username)) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        $user->setEnabled(false);
        $this->userManager->updateUser($user);
    }

    /**
     * Changes the password for the given user.
     *
     * @param string $username
     * @param string $password
     */
    public function changePassword($username, $password)
    {
        if (!$user = $this->userManager->findUserByUsername($username)) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        $user->setPlainPassword($password);
        $this->userManager->updateUser($user);
    }

    /**
     * Adds role to the given user.
     *
     * @param string $username
     * @param string $role
     *
     * @return Boolean true if role was added, false if user already had the role
     */
    public function addRole($username, $role)
    {
        if (!$user = $this->userManager->findUserByUsername($username)) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
        }

        if ($user->hasRole($role)) {
            return false;
        }

        $user->addRole($role);
        $this->userManager->updateUser($user);

        return true;
    }
}

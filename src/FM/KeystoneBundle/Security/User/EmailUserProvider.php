<?php

namespace FM\KeystoneBundle\Security\User;

class EmailUserProvider extends UserProvider
{
    /**
     * Finds a user by email or username
     *
     * @param string $username username of email
     *
     * @return UserInterface
     */
    public function findUserByUsername($username)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserBy(array('email' => $username));
        }

        return $this->findUserBy(array('username' => $username));
    }
}

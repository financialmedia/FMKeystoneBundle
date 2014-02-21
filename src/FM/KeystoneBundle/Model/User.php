<?php

namespace FM\KeystoneBundle\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see ../Resources/config/doctrine/User.orm.xml for mapping information
 */
class User implements AdvancedUserInterface, EquatableInterface, \Serializable
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string $username
     *
     * @Assert\NotBlank
     */
    protected $username;

    /**
     * @var string $email
     *
     * @Assert\NotBlank
     * @Assert\Email(message="email_invalid", checkMX=true)
     */
    protected $email;

    /**
     * @var string $password
     *
     * @Assert\NotBlank
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @var string $salt
     *
     * @Assert\NotBlank
     */
    protected $salt;

    /**
     * @var array $roles
     */
    protected $roles;

    /**
     * @var boolean $enabled
     */
    protected $enabled;

    /**
     * @var boolean $locked
     */
    protected $locked;

    /**
     * @var boolean $expired
     */
    protected $expired;

    /**
     * @var boolean $credentialsExpired
     */
    protected $credentialsExpired;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->roles = array();
        $this->credentialsExpired = false;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param  string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param  string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param  string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set plain password
     *
     * @param  string $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Get plain password
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set salt
     *
     * @param  string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set roles
     *
     * @param  array $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Adds role
     *
     * @param string $role
     */
    public function addRole($role)
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
    }

    /**
     * Remove role
     *
     * @param string $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set enabled
     *
     * @param  boolean $enabled
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set locked
     *
     * @param  boolean $locked
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Set expired
     *
     * @param  boolean $expired
     * @return User
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;

        return $this;
    }

    /**
     * Get accountExpired
     *
     * @return boolean
     */
    public function getAccountExpired()
    {
        return $this->expired;
    }

    /**
     * @inheritdoc
     */
    public function isAccountNonExpired()
    {
        if (true === $this->expired) {
            return false;
        }

        return true;
    }

    /**
     * Set credentialsExpired
     *
     * @param  boolean $credentialsExpired
     * @return User
     */
    public function setCredentialsExpired($credentialsExpired)
    {
        $this->credentialsExpired = $credentialsExpired;

        return $this;
    }

    /**
     * Get credentialsExpired
     *
     * @return boolean
     */
    public function isCredentialsExpired()
    {
        return $this->credentialsExpired;
    }

    /**
     * @inheritdoc
     */
    public function isCredentialsNonExpired()
    {
        if (true === $this->credentialsExpired) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isLocked()
    {
        return !$this->isAccountNonLocked();
    }

    public function isExpired()
    {
        return !$this->isAccountNonExpired();
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @inheritdoc
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->getPassword() !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        if ($this->isAccountNonExpired() !== $user->isAccountNonExpired()) {
            return false;
        }

        if ($this->isAccountNonLocked() !== $user->isAccountNonLocked()) {
            return false;
        }

        if ($this->isCredentialsNonExpired() !== $user->isCredentialsNonExpired()) {
            return false;
        }

        if ($this->isEnabled() !== $user->isEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->enabled,
            $this->locked,
            $this->expired,
            $this->credentialsExpired,
        ));
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->enabled,
            $this->locked,
            $this->expired,
            $this->credentialsExpired
        ) = unserialize($data);
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }
}

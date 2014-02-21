<?php

namespace FM\KeystoneBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FM\KeystoneBundle\Model\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Doctrine User Manager.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 */
class UserManager implements UserProviderInterface
{
    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $userClass;

    /**
     * Constructor.
     *
     * @param EncoderFactoryInterface $encoderFactory
     * @param EntityManager           $entityManager
     * @param string                  $userClass      Entity class, for example "FM\YourBundle\Entity\User"
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, EntityManager $entityManager, $userClass)
    {
        $this->encoderFactory = $encoderFactory;
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository($userClass);

        $metadata = $this->entityManager->getClassMetadata($userClass);
        $this->userClass = $metadata->getName();
    }

    /**
     * Returns the userClass
     *
     * @return string
     */
    protected function getClass()
    {
        return $this->userClass;
    }

    /**
     * Returns an empty user instance
     *
     * @param string        $username
     * @param string        $password
     * @param array<string> $roles
     *
     * @return User
     */
    public function createUser($username, $password, $roles = array())
    {
        $class = $this->getClass();

        /** @var User $user */
        $user = new $class;
        $user->setUsername($username);
        $user->setPlainPassword($password);
        $user->setRoles($roles);
        $user->setEnabled(true);

        return $user;
    }

    /**
     * @param $criteria
     *
     * @return User|null
     */
    protected function findUserBy($criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Finds a user by email
     *
     * @param string $email
     *
     * @return User
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(array('email' => $email));
    }

    /**
     * Finds a user by username
     *
     * @param string $username
     *
     * @return User
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(array('username' => $username));
    }

    /**
     * Finds a user either by email, or username
     *
     * @param string $usernameOrEmail
     *
     * @return User
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * Refreshes a user by User Instance
     *
     * It is strongly discouraged to use this method manually as it bypasses all ACL checks.
     *
     * @param UserInterface $user
     *
     * @throws UsernameNotFoundException When User has been removed and could not be reloaded
     * @throws UnsupportedUserException  When a User Instance is given which is not managed by this UserManager (so
     *                                   another Manager could try managing it)
     *
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        $class = $this->getClass();

        if (!$user instanceof $class) {
            throw new UnsupportedUserException('Account is not supported.');
        }

        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user))
            );
        }

        $refreshedUser = $this->findUserBy(array('id' => $user->getId()));
        if (null === $refreshedUser) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $user->getId()));
        }

        return $refreshedUser;
    }

    /**
     * Loads a user by username
     *
     * It is strongly discouraged to call this method manually as it bypasses all ACL checks.
     *
     * @param string $username
     *
     * @throws UsernameNotFoundException When no user with the name is found
     *
     * @return User
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }

    /**
     * @param User $user
     */
    public function updatePassword(User $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $user->setPassword($this->encodePassword($password, $user));
            $user->eraseCredentials();
        }
    }

    /**
     * @return string
     */
    public function generateSalt()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    /**
     * @param string $password
     * @param User   $user
     *
     * @return string
     */
    public function encodePassword($password, User $user)
    {
        if ($user->getSalt() == '') {
            $user->setSalt($this->generateSalt());
        }

        return $this->encoderFactory->getEncoder($user)->encodePassword($password, $user->getSalt());
    }

    /**
     * Updates a user.
     *
     * @param User    $user
     * @param boolean $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(User $user, $andFlush = true)
    {
        $this->updatePassword($user);

        $this->entityManager->persist($user);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Deletes a user.
     *
     * @param User    $user
     * @param boolean $andFlush Whether to flush the changes (default true)
     */
    public function deleteUser(User $user, $andFlush = true)
    {
        $this->entityManager->remove($user);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param User $user
     *
     * @return PasswordEncoderInterface
     */
    protected function getEncoder(User $user)
    {
        return $this->encoderFactory->getEncoder($user);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->getClass();
    }
}

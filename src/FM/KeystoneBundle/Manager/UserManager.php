<?php

namespace FM\KeystoneBundle\Manager;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
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
    protected $encoderFactory;
    protected $entityManager;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected $repository;
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
     * @return UserInterface
     */
    public function createUser()
    {
        $class = $this->getClass();
        $user = new $class;

        return $user;
    }

    protected function findUserBy($criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Finds a user by email
     *
     * @param string $email
     *
     * @return UserInterface
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
     * @return UserInterface
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
     * @return UserInterface
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * Refreshed a user by User Instance
     *
     * Throws UnsupportedUserException if a User Instance is given which is not
     * managed by this UserManager (so another Manager could try managing it)
     *
     * It is strongly discouraged to use this method manually as it bypasses
     * all ACL checks.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        $class = $this->getClass();
        if (!$user instanceof $class) {
            throw new UnsupportedUserException('Account is not supported.');
        }
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
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
     * It is strongly discouraged to call this method manually as it bypasses
     * all ACL checks.
     *
     * @param string $username
     *
     * @return UserInterface
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
     * {@inheritDoc}
     */
    public function updatePassword(UserInterface $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $user->setPassword($this->encodePassword($password, $user));
            $user->eraseCredentials();
        }
    }

    public function generateSalt()
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    public function encodePassword($password, UserInterface $user)
    {
        if ($user->getSalt() == '') {
            $user->setSalt($this->generateSalt());
        }

        return $this->encoderFactory->getEncoder($user)->encodePassword($password, $user->getSalt());
    }

    /**
     * Updates a user.
     *
     * @param UserInterface $user
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateUser(UserInterface $user, $andFlush = true)
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
     * @param UserInterface $user
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function deleteUser(UserInterface $user, $andFlush = true)
    {
        $this->entityManager->remove($user);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    protected function getEncoder(UserInterface $user)
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

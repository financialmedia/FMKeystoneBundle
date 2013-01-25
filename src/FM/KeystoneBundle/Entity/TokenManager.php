<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Entity;

use FM\KeystoneBundle\Model\Token;

use Doctrine\ORM\EntityManager;

use FM\KeystoneBundle\Security\Encoder\TokenEncoder;

class TokenManager
{
    protected $encoder;
    protected $entityManager;
    protected $repository;
    protected $tokenClass;

    /**
     * Constructor.
     *
     * @param TokenEncoder  $encoder
     * @param EntityManager $entityManager
     * @param string        $tokenClass
     */
    public function __construct(TokenEncoder $encoder, EntityManager $entityManager, $tokenClass)
    {
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository($tokenClass);

        $metadata = $this->entityManager->getClassMetadata($tokenClass);
        $this->tokenClass = $metadata->getName();
    }

    protected function getClass()
    {
        return $this->tokenClass;
    }

    /**
     * Returns an token instance
     *
     * @return UserInterface
     */
    public function createToken($user, $ttl = 3600)
    {
        $expires = time() + (int) $ttl;

        $class = $this->getClass();
        $token = new $class;
        $token->setHash($this->getEncoder()->generateTokenValue(get_class($user), $user->getUsername(), $user->getPassword(), $expires));
        $token->setExpiresAt(new \DateTime('@' . $expires));

        $this->updateToken($token);

        return $token;
    }

    protected function findTokenBy($criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Finds a token by token
     *
     * @param  string $token
     * @return Token
     */
    public function findTokenByToken($token)
    {
        return $this->findTokenBy(array('id' => $token));
    }

    /**
     * Updates a Token.
     *
     * @param Token   $token
     * @param Boolean $andFlush Whether to flush the changes (default true)
     */
    public function updateToken(Token $token, $andFlush = true)
    {
        $this->entityManager->persist($token);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function validate(Token $token)
    {
        return (bool) (new \DateTime() < $token->getExpiresAt());
    }

    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->getClass();
    }
}

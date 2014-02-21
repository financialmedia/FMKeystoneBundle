<?php

namespace FM\KeystoneBundle\Manager;

use Doctrine\ORM\EntityManager;
use FM\KeystoneBundle\Entity\Token;
use FM\KeystoneBundle\Security\Encoder\TokenEncoder;

class TokenManager
{
    protected $encoder;
    protected $entityManager;
    protected $repository;

    /**
     * Constructor.
     *
     * @param TokenEncoder  $encoder
     * @param EntityManager $entityManager
     */
    public function __construct(TokenEncoder $encoder, EntityManager $entityManager)
    {
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository('FMKeystoneBundle:Token');
    }

    /**
     * Returns a token instance
     *
     * @return UserInterface
     */
    public function createToken($user, $ttl = 3600)
    {
        $expires = time() + (int) $ttl;

        $token = new Token;
        $token->setHash($this->getEncoder()->generateTokenValue(get_class($user), $user->getUsername(), $user->getPassword(), $expires));
        $token->setExpiresAt(new \DateTime('@' . $expires));

        $this->updateToken($token);

        return $token;
    }

    public function findTokenBy($criteria)
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
        return new \DateTime() < $token->getExpiresAt();
    }

    public function getEncoder()
    {
        return $this->encoder;
    }
}

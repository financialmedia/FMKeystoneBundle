<?php

namespace FM\KeystoneBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FM\KeystoneBundle\Entity\Token;
use FM\KeystoneBundle\Security\Encoder\TokenEncoder;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenManager
{
    /**
     * @var TokenEncoder
     */
    protected $encoder;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
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
     * @param UserInterface $user
     * @param integer       $ttl
     *
     * @return Token
     */
    public function createToken($user, $ttl = 3600)
    {
        $expires = time() + (int) $ttl;

        $hash = $this->getEncoder()->generateTokenValue(
            get_class($user),
            $user->getUsername(),
            $user->getPassword(),
            $expires
        );

        $token = new Token;
        $token->setHash($hash);
        $token->setExpiresAt(new \DateTime('@' . $expires));

        $this->updateToken($token);

        return $token;
    }

    /**
     * @param $criteria
     *
     * @return Token|null
     */
    public function findTokenBy($criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Finds a token by token
     *
     * @param  string     $token
     * @return Token|null
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

    /**
     * @param Token $token
     *
     * @return boolean
     */
    public function validate(Token $token)
    {
        return new \DateTime() < $token->getExpiresAt();
    }

    /**
     * @return TokenEncoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }
}

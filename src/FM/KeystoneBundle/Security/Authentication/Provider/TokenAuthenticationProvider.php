<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use FM\KeystoneBundle\Entity\Token;
use FM\KeystoneBundle\Entity\TokenManager;
use FM\KeystoneBundle\Security\Authentication\Token\TokenToken;
use FM\KeystoneBundle\Authentication\Token\UsernamePasswordToken;

class TokenAuthenticationProvider implements AuthenticationProviderInterface
{
    private $hideUserNotFoundExceptions;
    private $tokenProvider;
    private $userProvider;
    private $userChecker;
    private $providerKey;

    /**
     * Constructor.
     *
     * @param TokenManager          $tokenProvider              A TokenManager
     * @param UserProviderInterface $userProvider               An UserProvider
     * @param UserCheckerInterface  $userChecker                An UserCheckerInterface interface
     * @param string                $providerKey                A provider key
     * @param Boolean               $hideUserNotFoundExceptions Whether to hide user not found exception or not
     */
    public function __construct(TokenManager $tokenProvider, UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, $hideUserNotFoundExceptions = true)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->tokenProvider = $tokenProvider;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->hideUserNotFoundExceptions = $hideUserNotFoundExceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $authToken = $token->getToken();
        if (empty($authToken)) {
            $authToken = 'NONE_PROVIDED';
        }

        $tokenEntity = $this->tokenProvider->findTokenByToken($authToken);
        if (!$tokenEntity) {
            throw new BadCredentialsException('Bad token');
        }

        if (false === $this->tokenProvider->validate($tokenEntity)) {
            throw new AuthenticationException('Token not valid');
        }

        $user = $this->retrieveUser($tokenEntity);

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('retrieveUser() must return a UserInterface.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->checkAuthentication($user, $tokenEntity, $token);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new BadCredentialsException('Bad credentials', 0, $e);
            }

            throw $e;
        }

        $authenticatedToken = new TokenToken($token->getToken(), $this->providerKey, $user->getRoles());
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof TokenToken && $this->providerKey === $token->getProviderKey();
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, Token $tokenEntity, TokenToken $token)
    {
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ("" === ($presentedToken = $token->getToken())) {
                throw new BadCredentialsException('The presented token cannot be empty.');
            }

            list($class, $username, $expires, $hash) = $this->tokenProvider->getEncoder()->decodeHash($tokenEntity->getHash());

            $username = base64_decode($username, true);

            if (false === $this->tokenProvider->getEncoder()->compareHashes($hash, $this->tokenProvider->getEncoder()->generateHash($class, $username, $user->getPassword(), $expires))) {
                throw new BadCredentialsException('The presented token is invalid.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveUser(Token $token)
    {
        $parts = $this->tokenProvider->getEncoder()->decodeHash($token->getHash());

        if (count($parts) !== 4) {
            throw new AuthenticationException('The hash is invalid.');
        }

        list($class, $username, $expires, $hash) = $parts;
        if (false === $username = base64_decode($username, true)) {
            throw new AuthenticationException('$username contains a character from outside the base64 alphabet.');
        }

        try {
            $user = $this->userProvider->loadUserByUsername($username);

            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
            }

            return $user;
        } catch (UsernameNotFoundException $notFound) {
            throw $notFound;
        } catch (\Exception $repositoryProblem) {
            throw new AuthenticationServiceException($repositoryProblem->getMessage(), $token, 0, $repositoryProblem);
        }
    }
}
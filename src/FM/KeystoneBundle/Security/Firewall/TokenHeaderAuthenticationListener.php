<?php

namespace FM\KeystoneBundle\Security\Firewall;

use FM\KeystoneBundle\Security\Authentication\Token\TokenToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class TokenHeaderAuthenticationListener implements ListenerInterface
{
    private $securityContext;
    private $authenticationManager;
    private $providerKey;
    private $logger;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, $providerKey, LoggerInterface $logger = null)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->logger = $logger;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->headers->has('X-Auth-Token')) {
            return;
        }

        $authToken = (string) $request->headers->get('X-Auth-Token');

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Post Authentication header found using token for token "%s"', $authToken));
        }

        try {
            $token = $this->authenticationManager->authenticate(new TokenToken($authToken, $this->providerKey));
            $this->securityContext->setToken($token);
        } catch (AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Authentication request failed for user "%s" using X-Auth-Token header: %s', $authToken, $failed->getMessage()));
            }

            throw $failed;
        }
    }
}

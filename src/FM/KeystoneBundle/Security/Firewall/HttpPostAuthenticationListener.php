<?php

namespace FM\KeystoneBundle\Security\Firewall;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

use FM\KeystoneBundle\Security\Authentication\Token\TokenToken;

class HttpPostAuthenticationListener implements ListenerInterface
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

    /**
     *
     * @see http://docs.openstack.org/api/openstack-identity-service/2.0/content/POST_authenticate_v2.0_tokens_Admin_API_Service_Developer_Operations-d1e1356.html
     *
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Http\Firewall\ListenerInterface::handle()
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() !== 'POST') {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if (false === $this->validateJson($data)) {
            throw new AuthenticationServiceException('Invalid JSON!');
            return;
        }

        if (isset($data['auth']['passwordCredentials'])) {
            // validate using Username and password
            $username = $data['auth']['passwordCredentials']['username'];
            $password = $data['auth']['passwordCredentials']['password'];

            $token = new UsernamePasswordToken($username, $password, $this->providerKey);

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Post Authentication body found using passwordCredentials for user "%s"', $username));
            }
        }
        else {
            // validate using token
            $token = $data['auth']['token']['id'];

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Post Authentication body found using token for token "%s"', $token));
            }

            $token = new TokenToken($token, $this->providerKey);
        }

        try {
            $token = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($token);
        } catch (AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Authentication request failed for user "%s" using POST and passwordCredentials: %s', $username, $failed->getMessage()));
            }

            throw new AccessDeniedHttpException('Unauthorized', $failed);
        }
    }

    /**
     * Validates the JSON
     *
     * @todo improve this! Maybe using a symfony validator, or maybe https://github.com/justinrainbow/json-schema
     * @todo authenticating using token is temporary disabled!
     *
     * @param array $data
     * @return boolean
     */
    protected function validateJson($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        if (!isset($data['auth']) || !is_array($data['auth'])) {
            return false;
        }

        if (isset($data['auth']['passwordCredentials'])) {
            if (!is_array($data['auth']['passwordCredentials'])) {
                return false;
            }

            if (!isset($data['auth']['passwordCredentials']['username'])) {
                return false;
            }

            if (!isset($data['auth']['passwordCredentials']['password'])) {
                return false;
            }
        }
        else {
            // TODO TEMPORARY DISABLED!
            return false;

            if (!isset($data['auth']['token']) || !is_array($data['auth']['token'])) {
                return false;
            }

            if (!isset($data['auth']['token']['id'])) {
                return false;
            }
        }

        return true;
    }
}

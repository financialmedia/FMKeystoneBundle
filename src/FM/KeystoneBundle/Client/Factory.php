<?php

namespace FM\KeystoneBundle\Client;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;

use FM\KeystoneBundle\Cache\CacheInterface;

/**
 * Factory service to create a Guzzle client with Keystone authentication
 * support. This factory also deals with expiring tokens, by automatically
 * re-authenticating.
 *
 * Usage:
 *
 * <code>
 *     $client = $factory->createClient($tokenUrl, $username, $password);
 * </code>
 *
 */
class Factory implements EventSubscriberInterface
{
    private $cache;
    private $logger;

    /**
     * Constructor.
     *
     * @param CacheInterface           $cache
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(CacheInterface $cache, \Psr\Log\LoggerInterface $logger = null)
    {
        $this->cache  = $cache;
        $this->logger = $logger;
    }

    /**
     * Creates a Guzzle client for communicating with a Keystone service.
     *
     * @param  string           $tokenUrl
     * @param  string           $username
     * @param  string           $password
     * @return Client
     * @throws RuntimeException When token could not be obtained
     */
    public function createClient($tokenUrl, $username, $password)
    {
        $client = new Client();
        $client->setTokenUrl($tokenUrl);
        $client->setKeystoneCredentials($username, $password);
        $client->getEventDispatcher()->addSubscriber($this);

        // set token
        if (null === $token = $this->getToken($client)) {
            throw new \RuntimeException(sprintf('Could not obtain token for %s', $tokenUrl));
        }

        $client->setToken($token);

        return $client;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'request.error' => array('onRequestError')
        );
    }

    /**
     * Listener for request errors.
     *
     * Catches 404 errors, since it's a bit harsh to throw an exception for
     * that. Also handles requests with expired authentication, by
     * reauthenticating and sending the request again.
     */
    public function onRequestError(Event $event)
    {
        // make sure no exceptions are thrown on 404 response codes
        if ($event['response']->getStatusCode() === 404) {
            $event->stopPropagation();
        }

        // if token validity expired, re-request with a new token.
        // TODO: our own keystone implementation returns 500 responses when not
        // authenticating properly, this has to be updated, after which this
        // handler needs to be updated.
        if (in_array($event['response']->getStatusCode(), array(401, 500))) {
            if ($this->logger) {
                $this->logger->addDebug('Token expired, fetching a new one');
            }

            // set new token in client
            $client = $event['request']->getClient();
            $token = $this->getToken($client, true);
            $client->setToken($token);

            // clone request and update token header
            $newRequest = clone $event['request'];
            $newRequest->setHeader('X-Auth-Token', $token->getId());
            $newResponse = $newRequest->send();

            // Set the response object of the request without firing more events
            $event['response'] = $newResponse;

            // Stop other events from firing when you override 401 responses
            $event->stopPropagation();
        }
    }

    /**
     * @return boolean
     */
    protected function tokenIsExpired(Token $token)
    {
        return new \DateTime() >= $token->getExpirationDate();
    }

    /**
     * Returns a token to use for the keystone service. Uses cached instance
     * whenever possible.
     *
     * @param  Client  $client   The client
     * @param  boolean $forceNew Whether to force creating a new token
     * @return Token
     */
    private function getToken(Client $client, $forceNew = false)
    {
        $tokenName = sprintf('keystone_token_%s', rawurlencode($client->getTokenUrl()));

        // see if token is in cache
        if ($this->cache->has($tokenName)) {
            $token = unserialize($this->cache->get($tokenName));
        }

        if (!isset($token) || !($token instanceof Token) || $this->tokenIsExpired($token) || $forceNew) {
            $token = $this->createToken($client);
            $this->cache->set($tokenName, serialize($token), $token->getExpirationDate()->getTimestamp() - time());
        }

        return $token;
    }

    /**
     * @throws RuntimeException when failed
     */
    protected function createToken(Client $client)
    {
        $data = array(
            'auth' => array(
                'passwordCredentials' => array(
                    'password' => $client->getKeystonePassword(),
                    'username' => $client->getKeystoneUsername()
                )
            )
        );

        if ($name = $client->getTenantName()) {
            $data['auth']['tenantName'] = $name;
        }

        // make sure token isn't sent in request
        $request = $client->post($client->getTokenUrl());
        $request->removeHeader('X-Auth-Token');
        $request->setBody(json_encode($data), 'application/json');

        $response = $request->send();
        $content = $response->json();

        $token = new Token($content['access']['token']['id'], new \DateTime($content['access']['token']['expires']));

        foreach ($content['access']['serviceCatalog'] as $catalog) {
            $token->addServiceCatalog($catalog['type'], $catalog['name'], $catalog['endpoints'][0]);
        }

        return $token;
    }
}

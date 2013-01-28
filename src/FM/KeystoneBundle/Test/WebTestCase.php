<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    protected $userProvider;
    protected $user;

    public function setUp()
    {
        $this->client = $this->createClient();
    }

    public function tearDown()
    {
        if (null !== $this->user) {
            $this->getUserProvider()->deleteUser($this->user);
        }
    }

    protected function getUserProvider()
    {
        if (null === $this->userProvider) {
            $this->userProvider = static::$kernel->getContainer()->get('fm_keystone.security.user_provider');
        }

        return $this->userProvider;
    }

    public function getRoute($name, array $parameters = array())
    {
        return static::$kernel->getContainer()->get('router')->generate($name, $parameters);
    }

    /**
     * Creates a new user and requests a valid token.
     *
     * @param  string $assoc When TRUE, returned objects will be converted into associative arrays.
     * @return mixed
     */
    protected function requestToken($assoc = false)
    {
        $this->user = $this->getUserProvider()->createUser(uniqid('test'), '1234', array('ROLE_API_USER'));

        $this->getUserProvider()->updateUser($this->user);

        $data = array(
            'auth' => array(
                'passwordCredentials' => array(
                    'username' => $this->user->getUsername(),
                    'password' => '1234',
                )
            )
        );

        $this->client->request('POST', $this->getRoute('get_token'), array(), array(), array(), json_encode($data));

        $result = json_decode($this->client->getResponse()->getContent(), $assoc);

        return $result;
    }

    protected function requestWithValidToken($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        $server = array_merge($server,
            array('HTTP_X-Auth-Token' => $this->requestToken()->access->token->id)
        );

        return $this->client->request('GET', '/api/properties/', array(), array(), $server);
    }
}

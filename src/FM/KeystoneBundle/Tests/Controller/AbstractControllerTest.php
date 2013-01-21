<?php

namespace FM\KeystoneBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected $user;
    protected $password;

    protected $client;
    protected $token;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        if (!$this->user) {

            $this->password = uniqid();

            $manager = static::$kernel->getContainer()->get('fm_keystone.user_manager');
            $manipulator = static::$kernel->getContainer()->get('fm_keystone.user_manipulator');

            if (null === $user = $manager->findUserByUsername('test')) {
                $user = $manipulator->create('test', $this->password, 'test@example.org', true);
            }

            $manipulator->addRole('test', 'ROLE_USER');
            $manipulator->changePassword('test', $this->password);

            $this->user = $user;
        }
    }

    public function getToken()
    {
        if (!$this->token) {
            $data = array(
                'auth' => array(
                    'passwordCredentials' => array(
                        'username' => $this->user->getUsername(),
                        'password' => $this->password,
                    )
                ),
            );


            $client = static::createClient();
            $client->request('POST', $this->getRoute('get_token'), array(), array(), array('Content-Type' => 'application/json'), json_encode($data));
            $response = json_decode($client->getResponse()->getContent(), true);

            $this->token = $response['access']['token']['id'];
        }

        return $this->token;
    }

    public function request($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $content = null)
    {
        if (!$this->client) {
            $this->client = static::createClient();
        }

        $server = array_merge(array('HTTP_X-Auth-Token' => $this->getToken()), $server);

        $this->client->request($method, $uri, $parameters, $files, $server, $content, false);

        return $this->client->getResponse();
    }

    public function getRoute($name, array $parameters = array())
    {
        return static::$kernel->getContainer()->get('router')->generate($name, $parameters);
    }
}

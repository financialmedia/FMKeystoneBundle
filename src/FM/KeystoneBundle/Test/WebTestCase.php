<?php

namespace FM\KeystoneBundle\Test;

use FM\KeystoneBundle\Util\UserManipulator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

abstract class WebTestCase extends BaseWebTestCase
{
    protected static $serviceType = 'compute';
    protected static $serviceName = 'foo';
    protected static $publicUrl = 'http://example.org/';
    protected static $adminUrl = 'http://secured.example.org/';

    protected $userProvider;
    protected $user;
    protected $serviceManager;
    protected $service;

    protected static $application;

    public function setUp()
    {
        $this->client = $this->createClient();

        static::$application = new Application(static::$kernel);
        static::$application->setAutoExit(false);
        static::$application->run(new StringInput('doctrine:database:create'));
        static::$application->run(new StringInput('doctrine:schema:update --force'));
    }

    public function tearDown()
    {
        if (null !== $this->user) {
            $this->getUserProvider()->deleteUser($this->user);
        }
    }

    protected function getUserProvider()
    {
        return static::$kernel->getContainer()->get('fm_keystone.security.user_provider');
    }

    /**
     * @return UserManipulator
     */
    protected function getUserManipulator()
    {
        return static::$kernel->getContainer()->get('fm_keystone.user_manipulator');
    }

    protected function getServiceManager()
    {
        if (null === $this->serviceManager) {
            $this->serviceManager = static::$kernel->getContainer()->get('fm_keystone.service_manager');
        }

        return $this->serviceManager;
    }

    protected function getUser()
    {
        if ($this->user === null) {
            if ($user = $this->getUserProvider()->findUserByEmail('test@example.org')) {
                $this->getUserProvider()->deleteUser($user);
            }

            $userManipulator = $this->getUserManipulator();

            $this->user = $userManipulator->create($username = uniqid('test'), '1234', 'test@example.org', true);

            $userManipulator->addRole($username, 'ROLE_USER');
        }

        return $this->user;
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
        $user = $this->getUser();

        $data = array(
            'auth' => array(
                'passwordCredentials' => array(
                    'username' => $user->getUsername(),
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

        return $this->client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }
}

<?php

namespace FM\KeystoneBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

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

    public function setUp()
    {
        $this->client = $this->createClient();
        $this->getService();
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

            $this->user = $this->getUserManipulator()->create(uniqid('test'), '1234', 'test@example.org', true);
        }

        return $this->user;
    }

    // protected function getService()
    // {
    //     if ($this->service === null) {
    //         $manager = $this->getServiceManager();

    //         // remove existing service first
    //         if ($service = $manager->findServiceBy(array('type' => static::$serviceType, 'name' => static::$serviceName))) {
    //             $manager->removeService($service);
    //         }

    //         $this->service = $manager->createService(static::$serviceType, static::$serviceName);
    //         $this->getServiceManager()->addEndpoint($this->service, static::$publicUrl, static::$adminUrl);
    //     }

    //     return $this->service;
    // }

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

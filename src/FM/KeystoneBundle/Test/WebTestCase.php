<?php

namespace FM\KeystoneBundle\Test;

use FM\KeystoneBundle\Manager\ServiceManager;
use FM\KeystoneBundle\Model\User;
use FM\KeystoneBundle\Security\User\UserProvider;
use FM\KeystoneBundle\Util\UserManipulator;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DomCrawler\Crawler;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var string
     */
    protected static $serviceType = 'compute';

    /**
     * @var string
     */
    protected static $serviceName = 'foo';

    /**
     * @var string
     */
    protected static $publicUrl = 'http://example.org/';

    /**
     * @var string
     */
    protected static $adminUrl = 'http://secured.example.org/';

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Application
     */
    protected static $application;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->client = $this->createClient();

        static::$application = new Application(static::$kernel);
        static::$application->setAutoExit(false);
        static::$application->run(new StringInput('doctrine:database:create'), new NullOutput());
        static::$application->run(new StringInput('doctrine:schema:update --force'), new NullOutput());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->client = null;

        if (null !== $this->user) {
            $this->getUserProvider()->deleteUser($this->user);
            $this->user = null;
        }
    }

    /**
     * @return UserProvider
     */
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

    /**
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        return static::$kernel->getContainer()->get('fm_keystone.service_manager');
    }

    /**
     * @return User
     */
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

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function getRoute($name, array $parameters = array())
    {
        return static::$kernel->getContainer()->get('router')->generate($name, $parameters);
    }

    /**
     * Creates a new user and requests a valid token.
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    protected function requestToken()
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

        $result = json_decode($this->client->getResponse()->getContent(), true);

        if (is_array($result)) {
            return $result;
        }

        throw new \UnexpectedValueException(
            sprintf(
                'Unexpected response (%d): %s',
                $this->client->getResponse()->getStatusCode(),
                $this->client->getResponse()->getContent()
            )
        );
    }

    /**
     * @param string  $method
     * @param string  $uri
     * @param array   $parameters
     * @param array   $files
     * @param array   $server
     * @param string  $content
     * @param boolean $changeHistory
     *
     * @return Crawler
     */
    protected function requestWithValidToken(
        $method,
        $uri,
        array $parameters = array(),
        array $files = array(),
        array $server = array(),
        $content = null,
        $changeHistory = true
    ) {
        $server = array_merge(
            $server,
            array('HTTP_X-Auth-Token' => $this->requestToken()['access']['token']['id'])
        );

        return $this->client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }
}

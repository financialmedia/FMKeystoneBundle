<?php

namespace FM\KeystoneBundle\Tests\Controller;

use FM\KeystoneBundle\Test\WebTestCase;

class TokenControllerTest extends WebTestCase
{
    const HTTP_FORBIDDEN          = 403;
    const HTTP_METHOD_NOT_ALLOWED = 405;

    public function testGetTokenWithInvalidUsernameAndPasswordReturnsError()
    {
        $data = array(
            'auth' => array(
                'passwordCredentials' => array(
                    'username' => 'non-existing-user',
                    'password' => '1234' . uniqid(),
                )
            )
        );

        $this->client->request('POST', $this->getRoute('get_token'), array(), array(), array(), json_encode($data));

        $this->assertEquals(self::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testGetTokenWithoutPostDataReturnsError()
    {
        $this->client->request('POST', $this->getRoute('get_token'), array(), array(), array(), '');

        $this->assertEquals(self::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testGetTokenWithGETReturnsError()
    {
        $this->client->request('GET', $this->getRoute('get_token'), array(), array(), array(), '');

        $this->assertEquals(self::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode());
    }

    public function testGetTokenWithValidUsernameAndPasswordReturnsToken()
    {
        $result = $this->requestToken();

        $this->assertObjectHasAttribute('access', $result);
        $this->assertObjectHasAttribute('token', $result->access);
        $this->assertObjectHasAttribute('id', $result->access->token);

        return $result->access->token->id;
    }
}

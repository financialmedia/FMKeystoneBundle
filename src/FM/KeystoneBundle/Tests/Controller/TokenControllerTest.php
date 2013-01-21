<?php

namespace FM\KeystoneBundle\Tests\Controller;

class TokenControllerTest extends AbstractControllerTest
{
    public function testCreateToken()
    {
        $client = static::createClient();

        // login failure
        $client->request('POST', $this->getRoute('get_token'), array(), array(), array('Content-Type' => 'application/json'), '');
        $this->assertEquals(500, $client->getResponse()->getStatusCode());

        $data = array(
            'auth' => array(
                'passwordCredentials' => array(
                    'username' => $this->user->getUsername(),
                    'password' => $this->password,
                )
            ),
        );

        $client->request('POST', $this->getRoute('get_token'), array(), array(), array('Content-Type' => 'application/json'), json_encode($data));
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('access', $response);
        $this->assertArrayHasKey('token', $response['access']);
    }
}

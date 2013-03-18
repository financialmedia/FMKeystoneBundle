<?php

namespace FM\KeystoneBundle\Client;

use Guzzle\Http\Client as GuzzleClient;

/**
 * Extended Guzzle HTTP client for transparent communication with a keystone
 * service.
 *
 * Basic usage:
 * <code>
 *     $token = obtainToken();
 *
 *     $client = new Client();
 *     $client->setTokenUrl('http://keystone-service.com/tokens');
 *     $client->setKeystoneCredentials('username', 'password');
 *     $client->setToken($token);
 * </code>
 *
 * Now the client automatically uses the keystone public-url, and adds the
 * appropriate token header.
 *
 * Note that you still have to deal with expired tokens, by obtaining a new
 * token and giving it to the client.
 */
class Client extends GuzzleClient
{
    protected $token;
    protected $tokenUrl;
    protected $publicUrl;
    protected $tenantName;
    protected $keystoneUsername;
    protected $keystonePassword;

    public function setKeystoneCredentials($username, $password)
    {
        $this->keystoneUsername = $username;
        $this->keystonePassword = $password;
    }

    public function getKeystoneUsername()
    {
        return $this->keystoneUsername;
    }

    public function getKeystonePassword()
    {
        return $this->keystonePassword;
    }

    public function setTenantName($name)
    {
        $this->tenantName = $name;
    }

    public function getTenantName()
    {
        return $this->tenantName;
    }

    public function setTokenUrl($tokenUrl)
    {
        $this->tokenUrl = $tokenUrl;
    }

    public function getTokenUrl()
    {
        return $this->tokenUrl;
    }

    public function setToken(Token $token)
    {
        $this->token = $token;

        // set new default header
        $this->getDefaultHeaders()->set('X-Auth-Token', $token->getId());

        // set public url
        $catalog = array_change_key_case($token->getServiceCatalog('object-store'), CASE_LOWER);
        $this->setPublicUrl(rtrim($catalog['publicurl'], '/'));
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getTokenId()
    {
        return $this->token->getId();
    }

    public function setPublicUrl($url)
    {
        $this->publicUrl = $url;
        $this->setBaseUrl($url);
    }

    public function getPublicUrl()
    {
        return $this->publicUrl;
    }

    public function getBaseUrl($expand = true)
    {
        if (!$this->publicUrl) {
            $this->dispatch(
                'client.initialize',
                array(
                    'client' => $this
                )
            );
        }

        return parent::getBaseUrl($expand);
    }
}

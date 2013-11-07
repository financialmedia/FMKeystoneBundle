<?php

namespace FM\KeystoneBundle\Model;

class Service
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Endpoint[]
     */
    protected $endpoints;

    /**
     * Constructor
     */
    public function __construct($name, $type)
    {
        $this->name      = $name;
        $this->type      = $type;
        $this->endpoints = array();
    }

    /**
     * Set type
     *
     * @param  string  $type
     * @return Service
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return Service
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add endpoint
     *
     * @param  string $publicUrl
     * @param  string $adminUrl
     * @return Service
     */
    public function addEndpoint($publicUrl, $adminUrl)
    {
        $endpoint = new Endpoint();
        $endpoint->setPublicUrl($publicUrl);
        $endpoint->setAdminUrl($adminUrl);
        $endpoint->setService($this);

        $this->endpoints[] = $endpoint;

        return $this;
    }

    /**
     * Get endpoints
     *
     * @return Endpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }
}

<?php

namespace FM\KeystoneBundle\Manager;

use FM\KeystoneBundle\Model\Service;
use Symfony\Component\HttpFoundation\Request;

class ServiceManager
{
    /**
     * @var string[]
     */
    protected $types = array();

    /**
     * @var Service[]
     */
    protected $services = array();

    /**
     * @param array $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Add service
     *
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * Returns all services
     *
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    public function findServiceByEndpoint($url)
    {
        $url = $this->getNormalizedUrl($url);

        foreach ($this->services as $service) {
            foreach ($service->getEndpoints() as $endpoint) {
                $endpointUrl = $this->getNormalizedUrl($endpoint->getPublicUrl());
                if (substr($url, 0, strlen($endpointUrl)) === $endpointUrl) {
                    return $service;
                }
            }
        }
    }

    protected function getNormalizedUrl($url)
    {
        return Request::create($url)->getUri();
    }
}

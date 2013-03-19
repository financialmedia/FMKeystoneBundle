<?php

namespace FM\KeystoneBundle\Model;

class Endpoint
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $adminUrl;

    /**
     * @var string
     */
    protected $publicUrl;

    /**
     * @var Service
     */
    protected $service;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set publicUrl
     *
     * @param  string   $publicUrl
     * @return Endpoint
     */
    public function setPublicUrl($publicUrl)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    /**
     * Get publicUrl
     *
     * @return string
     */
    public function getPublicUrl()
    {
        return $this->publicUrl;
    }

    /**
     * Set adminUrl
     *
     * @param  string   $adminUrl
     * @return Endpoint
     */
    public function setAdminUrl($adminUrl)
    {
        $this->adminUrl = $adminUrl;

        return $this;
    }

    /**
     * Get adminUrl
     *
     * @return string
     */
    public function getAdminUrl()
    {
        return $this->adminUrl;
    }

    /**
     * Set service
     *
     * @param  \FM\KeystoneBundle\Entity\Service $service
     * @return Endpoint
     */
    public function setService(\FM\KeystoneBundle\Entity\Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \FM\KeystoneBundle\Entity\Service
     */
    public function getService()
    {
        return $this->service;
    }
}

<?php

namespace FM\KeystoneBundle\Model;

class Service
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $endpoints;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->endpoints = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set type
     *
     * @param string $type
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
     * @param string $name
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
     * Add endpoints
     *
     * @param \FM\KeystoneBundle\Entity\Endpoint $endpoints
     * @return Service
     */
    public function addEndpoint(\FM\KeystoneBundle\Entity\Endpoint $endpoints)
    {
        $this->endpoints[] = $endpoints;

        return $this;
    }

    /**
     * Remove endpoints
     *
     * @param \FM\KeystoneBundle\Entity\Endpoint $endpoints
     */
    public function removeEndpoint(\FM\KeystoneBundle\Entity\Endpoint $endpoints)
    {
        $this->endpoints->removeElement($endpoints);
    }

    /**
     * Get endpoints
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }
}

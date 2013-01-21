<?php

namespace FM\KeystoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Endpoint
 *
 * @ORM\Table(name="endpoint")
 * @ORM\Entity
 */
class Endpoint
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_url", type="string", length=255)
     */
    private $adminUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="public_url", type="string", length=255)
     */
    private $publicUrl;


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
     * Set adminUrl
     *
     * @param string $adminUrl
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
     * Set publicUrl
     *
     * @param string $publicUrl
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
}

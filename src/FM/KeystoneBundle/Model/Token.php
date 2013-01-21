<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Model;

/**
 * @see ../Resources/config/doctrine/Token.orm.xml for mapping information
 */
class Token
{
    protected $id;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var \DateTime
     */
    protected $expiresAt;

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return Token
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     * @return Token
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function isExpired()
    {
        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return true;
        }

        return false;
    }
}
<?php

/**
 * @author Jeroen Fiege <jeroen@financial-media.nl>
 * @copyright Financial Media BV <http://financial-media.nl>
 */

namespace FM\KeystoneBundle\Model;

interface TokenInterface
{
    public function setHash($hash);
    public function getHash();
    public function setExpiresAt($expiresAt);
    public function getExpiresAt();
    public function isExpired();
}
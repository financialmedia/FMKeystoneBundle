<?php

namespace FM\KeystoneBundle\Security\Encoder;

class TokenEncoder
{
    const HASH_DELIMITER = ':';

    protected $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public function decodeHash($hash)
    {
        return explode(self::HASH_DELIMITER, base64_decode($hash));
    }

    public function encodeHash(array $parts)
    {
        return base64_encode(implode(self::HASH_DELIMITER, $parts));
    }

    public function generateHash($class, $username, $password, $expires)
    {
        return hash('sha256', $class.$username.$password.$expires.$this->secret);
    }

    public function compareHashes($hash1, $hash2)
    {
        if (strlen($hash1) !== $c = strlen($hash2)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < $c; $i++) {
            $result |= ord($hash1[$i]) ^ ord($hash2[$i]);
        }

        return 0 === $result;
    }

    public function generateTokenValue($class, $username, $password, $expires)
    {
        return $this->encodeHash(array(
            $class,
            base64_encode($username),
            $expires,
            $this->generateHash($class, $username, $password, $expires),
        ));
    }
}

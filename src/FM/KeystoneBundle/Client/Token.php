<?php

namespace FM\KeystoneBundle\Client;

class Token implements \Serializable
{
    private $id;
    private $expires;
    private $catalogs;

    public function __construct($id, \DateTime $expires)
    {
        $this->id = $id;
        $this->expires = $expires;
        $this->catalogs = array();
    }

    public function getId()
    {
        return $this->id;
    }

    public function addServiceCatalog($type, $name, array $endpoints)
    {
        if (!array_key_exists($type, $this->catalogs)) {
            $this->catalogs[$type] = array();
        }

        $this->catalogs[$type][$name] = $endpoints;
    }

    public function getServiceCatalog($type, $name = null)
    {
        return is_null($name) ? current($this->catalogs[$type]) : $this->catalogs[$type][$name];
    }

    public function getExpirationDate()
    {
        return $this->expires;
    }

    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'expires' => $this->expires,
            'catalogs' => $this->catalogs
        ));
    }

    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->id = $data['id'];
        $this->expires = $data['expires'];
        $this->catalogs = $data['catalogs'];
    }
}

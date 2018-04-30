<?php

namespace App\Entity;

class User implements UserInterface
{
    private $uaid;
    private $netid;


    public function _contruct($uaid) {
        $this->uaid = $uaid
    }

    public function getId()
    {
        return $this->uaid;
    }

    public function getUsername()
    {
        return $this->netid;
    }
}

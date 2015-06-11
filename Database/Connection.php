<?php

namespace Database;

class Connection {

    protected $name, $username, $password;

    protected $uniqid;


    public function __construct($name, $username, $password) {
        $this->name = $name;
        $this->username = $username;
        $this->password = $password;
        $this->uniqid = uniqid();
    }
}
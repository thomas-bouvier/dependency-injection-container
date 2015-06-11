<?php

namespace Database;

class Model {

    protected $connection;

    protected $uniqid;


    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->uniqid = uniqid();
    }
}
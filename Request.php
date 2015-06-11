<?php

class Request {

    protected $uniqid;


    public function __construct() {
        $this->uniqid = uniqid();
    }
}
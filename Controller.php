<?php

class Controller {

    protected $name, $model;


    public function __construct($name = 'Default') {
        $this->name = $name;
    }
}
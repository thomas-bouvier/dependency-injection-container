<?php

class Controller {

    protected $name, $model;


    public function __construct($name = 'Default', Database\Model $model) {
        $this->name = $name;
        $this->model = $model;
    }
}
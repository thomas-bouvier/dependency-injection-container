<?php

require 'App.php';
require 'Request.php';
require 'Controller.php';

require 'Database\Connection.php';
require 'Database\Model.php';


    // without dependency injection
    new Database\Model(new Database\Connection('dbname', 'root', 'root'));

    // dependency injection container initialization
    $dic = new App;

    $dic->set('connection', function() {
        return new Database\Connection('dbname', 'root', 'root');
    });

    $dic->set('model', function() use ($dic) {
        return new Database\Model($dic->get('connection'));
    }, false);

    // same instance
    var_dump($dic->get('connection'));
    var_dump($dic->get('connection'));

    // different instances
    var_dump($dic->get('model'));
    var_dump($dic->get('model'));

    $connection = new Database\Connection('dbname', 'admin', 'admin');
    $dic->setInstance($connection);

    var_dump($dic->get('connection'));

    // both work, but the instance is different
    // strtolower($key) solves it
    var_dump($dic->get('Request'));
    var_dump($dic->get('request'));

    var_dump($dic->get('Controller'));
    var_dump($dic->get('controller'));
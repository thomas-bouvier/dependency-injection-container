<?php

require 'Container.php';
require 'Request.php';
require 'Controller.php';

require 'Database\Connection.php';
require 'Database\Model.php';

$container = new Container;

$container['connection'] = function() {
    return new Database\Connection('database', 'root', 'root');
};

var_dump($container['connection']);

$container['model'] = $container->factory(function() use ($container) {
    return new Database\Model($container['connection']);
});

var_dump($container['model']);

$container['request'] = $container->instance(new Request);

var_dump($container['request']);

var_dump($container['controller']);
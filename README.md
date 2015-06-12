# Dependency Injection Container

Creating a container is a matter of creating a ``Container`` instance :

    $container = new Container();

Services
--------

Services are defined by closures that return an instance of an object :

    $container['connection'] = function() {
        return new Database\Connection('database', 'root', 'root');
    };
    
As objects are only created when you get them, the order of the definitions does not matter.

Factory Services
----------------

The container returns the same instance each time you get a service. You can choose to get a new instance for all calls by using the ``factory()`` method :

    $container['connection'] = $container->factory(function() {
        return new Database\Connection('database', 'root', 'root');
    });

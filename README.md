# Dependency Injection Container

Usage
-----

Creating a container is a matter of creating a ``IoC\Container`` instance :

    $container = new IoC\Container();

Defining services
-----------------

Services are defined by closures that return an instance of an object :

    $container->bind('connection', function() {
        return new Connection('database', 'root', 'root');
    });

The above call is roughly equivalent to the following code, as the ``Container`` implements the ``ArrayAccess`` interface :

    $container['connection'] = function() {
        return new Connection('database', 'root', 'root');
    };
    
As objects are only created when you get them, the order of the definitions does not matter.

Retrieving a service is also very easy :

    $db = $container->resolve('connection');
    
Notice that the above call is roughly equivalent to the following code :
    
    $db = $container['connection'];

Defining factory services
-------------------------

The container returns the same instance each time you get a service. You can choose to get a new instance for all calls by providing a third parameter :

    $container->bind('connection', function() {
        return new Connection('database', 'root', 'root');
    }, true);
    
Now, each call to ``$container['connection']`` returns a new instance of the connection.

Defining instances
------------------

The container allows the storage of instances :

    $db = new Connection('database', 'root', 'root');
    $container->bind('connection', $db);
    
Each call to ``$container['connection']`` then returns ``$db``.
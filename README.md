# Dependency Injection Container

This Dependency Injection Container manages two kind of data : **services** and **parameters**.

## Usage

Creating a container is a matter of creating a ``DIC\Container`` instance :

```php
use DIC\Container;
$container = new Container();
```

### Defining services

Services are defined by **closures** that return an instance of an object :

```php
$container->bind('connection', function() {
    return new Connection('database_name', 'root', 'root');
});
```

The above call is equivalent to the following code, as the ``Container`` implements the ``ArrayAccess`` interface :

```php
$container['connection'] = function() {
    return new Connection('database_name', 'root', 'root');
};
```
    
As objects are only created when you get them, the order of the definitions does not matter.

Retrieving a service is also very easy :

```php
$database_connection = $container->resolve('connection');
```
    
The above call is equivalent to the following code :

```php
$database_connection = $container['connection'];
```

Notice that the closure has access to the current container instance, allowing references to other services or parameters :

```php
$container['foo'] = function() {
    return new Foo();
};
$container['bar'] = function($c) {
    return new Bar($c['foo']);
};
```

### Defining factory services

The container returns the **same instance** each time you get a service. You can choose to get a new instance for all calls by providing a third parameter ``factory`` :

```php
$container->bind('connection', function() {
    return new Connection('database_name', 'root', 'root');
}, true);
```

This call can also be written by wrapping the closure with the ``factory()`` method :

```php
$container['connection'] = $container->factory(function() {
    return new Connection('database_name', 'root', 'root');
});
```

Now, each call to ``$container['connection']`` returns a new instance of the connection.

### Defining parameters

Defining a parameter allows to store global values in the container :

```php
$container['database_name'] = 'DB_PROJECT';
```
    
### Resolving undefined dependencies

If you access a service which has not previously been defined, the container will attempt to build the dependencies using reflection.
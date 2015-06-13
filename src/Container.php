<?php

namespace IoC;

require 'ClassNotInstantiableException.php';

use ReflectionClass;

/**
 * Container main class.
 * Also accessible as an array.
 *
 * @author yach
 * @package IoC
 */
class Container implements \ArrayAccess {

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @var array
     */
    protected $instances = [];


    /**
     * Assigns an instance, a resolver or a class to be instantiated
     * to the specified unique identifier.
     *
     * Resolvers are defined as Closures.
     *
     * @param mixed $key
     *      the unique identifier for the resolver
     * @param mixed $value
     *      the Closure to define the resolver or a parameter
     */
    public function offsetSet($key, $value) {
        $this->bind($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $singleton
     */
    public function bind($key, $value, $singleton = false) {
        $this->bindings[$key] = compact('value', 'singleton');
    }

    /**
     * @param $key
     * @param $value
     */
    public function singleton($key, $value) {
        $this->bind($key, $value, true);
    }

    /**
     * Marks a resolver being a factory service.
     *
     * @param $resolver
     *      the resolver to be used as a factory service
     * @return mixed
     *      the passed resolver
     */
    public function factory($resolver) {
        //TODO
    }

    /**
     * Retrieves the object or the parameter associated to the specified identifier.
     *
     * @param mixed $key
     *      the unique identifier for the object to retrieve.
     * @return mixed
     */
    public function offsetGet($key) {
        return $this->resolve($key);
    }

    /**
     * @param $key
     * @return null
     */
    public function getBinding($key) {
        if (!array_key_exists($key, $this->bindings)) {
            return null;
        }
        return $this->bindings[$key];
    }

    /**
     * @param $key
     * @param array $args
     * @return object
     * @throws ClassNotInstantiableException
     */
    public function resolve($key, $args = []) {
        $class = $this->getBinding($key);
        if ($class === null) {
            $class = $key;
        }
        if ($this->isSingleton($key) && $this->isSingletonResolved($key)) {
            return $this->getSingletonInstance($key);
        }
        if (isset($this->bindings[$key]['value']) && is_callable($this->bindings[$key]['value'])) {
            return $this->storeInstanceIfSingleton($key, $this->bindings[$key]['value']());
        }
        if (isset($this->bindings[$key]['value']) && is_object($this->bindings[$key]['value'])) {
            return $this->storeInstanceIfSingleton($key, $this->bindings[$key]['value']);
        }
        return $this->storeInstanceIfSingleton($key, $this->buildObject($class, $args));
    }

    /**
     * @param $key
     * @param $obj
     * @return mixed
     */
    protected function storeInstanceIfSingleton($key, $obj) {
        if ($this->isSingleton($key)) {
            $this->instances[$key] = $obj;
        }
        return $obj;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isSingleton($key) {
        $binding = $this->getBinding($key);
        if ($binding === null) {
            return false;
        }
        return $binding['singleton'];
    }

    /**
     * @param $key
     * @return bool
     */
    public function isSingletonResolved($key) {
        return $this->isSingleton($key) && array_key_exists($key, $this->instances);
    }

    /**
     * @param $key
     * @return null
     */
    public function getSingletonInstance($key) {
        if ($this->isSingletonResolved($key)) {
            return $this->instances[$key];
        }
        return null;
    }

    /**
     * @param $class
     * @param array $args
     * @return object
     * @throws ClassNotInstantiableException
     */
    protected function buildObject($class, $args = []) {
        if (is_array($class)) {
            $className = $class['value'];
        } else {
            $className = $class;
        }
        $reflector = new ReflectionClass($className);
        if (!$reflector->isInstantiable()) {
            throw new ClassNotInstantiableException("Class [$className] is not a resolvable dependency.");
        }
        if ($reflector->getConstructor() !== null) {
            $dependencies = $reflector->getConstructor()->getParameters();
            $this->buildDependencies($args, $dependencies);
        }
        return $reflector->newInstanceArgs($args);
    }

    /**
     * @param $args
     * @param $dependencies
     * @return mixed
     */
    protected function buildDependencies($args, $dependencies) {
        foreach ($dependencies as $dependency) {
            $class = $dependency->getClass();
            if ($dependency->isOptional() || $dependency->isArray() || $class === null) {
                continue;
            }
            if (get_class($this) === $class->getName()) {
                array_unshift($args, $this);
                continue;
            }
            array_unshift($args, $this->resolve($class->getName()));
        }
        return $args;
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param mixed $key
     *      the unique identifier for the parameter or the object
     */
    public function offsetUnset($key) {
        unset($this->bindings[$key]);
        if ($this->isSingleton($key)) {
            unset($this->instances[$key]);
        }
    }

    /**
     * Checks whether or not a parameter or an object is set.
     *
     * @param mixed $key
     *      the unique identifier for the object or parameter to check for
     * @return bool
     */
    public function offsetExists($key) {
        return array_key_exists($key, $this->bindings);
    }

    /**
     * @return array
     */
    public function getBindings() {
        return $this->bindings;
    }
}
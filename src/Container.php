<?php

namespace DIC;

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
     * @var
     */
    protected $factories;


    /**
     *
     */
    public function __construct() {
        $this->factories = new \SplObjectStorage();
    }

    /**
     * @param $key
     * @param $value
     * @param bool $factory
     */
    public function bind($key, $value, $factory = false) {
        $this->bindings[$key] = compact('value', 'factory');
    }

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
     * @param array $args
     * @return object
     * @throws ClassNotInstantiableException
     */
    protected function resolve($key, $args = []) {
        if (!$this->isFactory($key) && $this->isResolved($key)) {
            return $this->getInstance($key);
        }
        if (isset($this->bindings[$key]['value'])) {
            $value = $this->bindings[$key]['value'];
            if (!is_object($value)) {
                return $value;
            }
            if ($this->isCallable($value)) {
                return $this->storeInstanceIfNotFactory($key, $value($this));
            }
        }
        return $this->storeInstanceIfNotFactory($key, $this->buildObject($key, $args));
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
     * Marks a resolver being a factory service.
     *
     * @param $callable
     *      the resolver to be used as a factory service
     * @return mixed
     *      the passed resolver
     */
    public function factory($callable) {
        if (!$this->isCallable($callable)) {
            throw new \InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }
        $this->factories->attach($callable);
        return $callable;
    }

    /**
     * @param $key
     * @return null
     */
    protected function getBinding($key) {
        if (!array_key_exists($key, $this->bindings)) {
            return null;
        }
        return $this->bindings[$key];
    }

    /**
     * @param $key
     * @param $obj
     * @return mixed
     */
    protected function storeInstanceIfNotFactory($key, $obj) {
        if (!$this->isFactory($key)) {
            $this->instances[$key] = $obj;
        }
        return $obj;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isFactory($key) {
        if (!array_key_exists($key, $this->bindings)) {
            return false;
        }
        $binding = $this->bindings[$key];
        if (!is_object($binding['value'])) {
            return false;
        }
        if ($this->factories->contains($binding['value'])) {
            $this->factories->detach($binding['value']);
            $binding['factory'] = true;
        }
        return $binding['factory'];
    }

    /**
     * @param $key
     * @return bool
     */
    protected function isResolved($key) {
        return !$this->isFactory($key) && array_key_exists($key, $this->instances);
    }

    /**
     * @param $key
     * @return null
     */
    protected function getInstance($key) {
        if ($this->isResolved($key)) {
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
        if (!$this->isFactory($key)) {
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
     * @param $value
     * @return bool
     */
    protected function isCallable($value) {
        return is_object($value) && method_exists($value, '__invoke');
    }
}
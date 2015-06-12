<?php

/**
 * Container main class.
 * Also accessible as an array.
 *
 * @author yach
 */
class Container implements ArrayAccess {

    protected $registry = [];
    protected $factories = [];
    protected $raw = [];
    protected $keys = [];


    /**
     * Assigns an object, a resolver or a parameter to the specified identifier.
     *
     * Resolvers are defined as Closures.
     *
     * @param mixed $id
     *      the unique identifier for the resolver
     * @param mixed $resolver
     *      the Closure to define the resolver or a parameter
     */
    public function offsetSet($id, $resolver)
    {
        $this->registry[$id] = $resolver;
        $this->keys[$id] = true;
    }

    /**
     * Retrieves the object or the parameter associated to the specified identifier.
     *
     * If no object matches to the specified identifier,
     * an instance is created using reflection.
     *
     * @param mixed $id
     *      the unique identifier for the object to retrieve.
     * @return mixed
     */
    public function offsetGet($id)
    {
        $id = strtolower($id);

        if (!isset($this->keys[$id])) {
            $this->buildNewInstance($id);
        }

        // if the resolver is not registered as a factory service,
        // the same instance is returned
        if (isset($this->raw[$id]) || !is_object($this->registry[$id]) || in_array($this->registry[$id], $this->raw)) {
            return $this->registry[$id];
        }

        // if the resolver is registered as a factory service,
        // a new instance is returned
        if (in_array($this->registry[$id], $this->factories)) {
            return $this->registry[$id]();
        }

        $raw = $this->registry[$id];
        $this->registry[$id] = $raw();
        $this->raw[$id] = $raw;

        return $this->registry[$id];
    }

    /**
     * Marks a resolver being a factory service.
     *
     * @param $resolver
     *      the resolver to be used as a factory service
     * @return mixed
     *      the passed resolver
     */
    public function factory($resolver)
    {
        $this->factories[] = $resolver;
        return $resolver;
    }

    /**
     *
     *
     * @param $instance
     * @return mixed
     */
    public function instance($instance)
    {
        $this->raw[] = $instance;
        return $instance;
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param mixed $id
     *      the unique identifier for the parameter or the object
     */
    public function offsetUnset($id)
    {
        unset($this->keys[$id], $this->registry[$id], $this->factories[$id], $this->raw[$id]);
    }

    /**
     * Checks whether or not a parameter or an object is set.
     *
     * @param mixed $id
     *      the unique identifier for the object or parameter to check for
     * @return bool
     */
    public function offsetExists($id)
    {
        return isset($this->keys[$id]);
    }

    /**
     * Builds an instance of the specified class name (unique
     * identifier) using reflection.
     *
     * The instance is then assigned to its unique identifier.
     *
     * @param $id
     *      the unique identifier
     */
    public function buildNewInstance($id)
    {
        $reflect = new ReflectionClass($id);

        if ($reflect->isInstantiable()) {

            $id = strtolower($id);
            $constructor = $reflect->getConstructor();

            if ($constructor) {

                $parameters = $constructor->getParameters();
                $parameters_constructor = [];

                foreach ($parameters as $parameter)
                {
                    if ($parameter->getClass()) {
                        $parameters_constructor[] = $this[$parameter->getClass()->getName()];
                    } else {
                        $parameters_constructor[] = $parameter->getDefaultValue();
                    }
                }

                $this[$id] = $this->raw[$id] = $reflect->newInstanceArgs($parameters_constructor);

            } else {

                $this[$id] = $this->raw[$id] = $reflect->newInstance();
            }
        } else {

            throw new RuntimeException(sprintf('Could not resolve dependency : "%s" is not an instantiable Class.', $id));
        }
    }
}
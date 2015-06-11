<?php

class App {

    // $key(string) => $resolver(Closure)
    protected $registry = [];

    // $key(string) => $resolver(Closure)
    protected $factories = [];

    // $key(string) => $instance(Object)
    protected $instances = [];


    /**
     * @param $key
     * @param callable $resolver
     * @param bool $oneInstance
     */
    public function set($key, Callable $resolver, $oneInstance = true) {
        if ($oneInstance) {
            $this->registry[$key] = $resolver;
        } else {
            $this->factories[$key] = $resolver;
        }
    }

    /**
     * @param $instance
     */
    public function setInstance($instance) {
        $reflect = new ReflectionClass($instance);
        $this->instances[$reflect->getName()] = $instance;
    }

    /**
     * @param $key
     * @return mixed
     * @throws Exception
     */
    public function get($key) {
        if (isset($this->factories[$key])) {
            return $this->factories[$key]();
        }
        if (!isset($this->instances[$key])) {
            if (isset($this->registry[$key])) {
                $this->instances[$key] = $this->registry[$key]();
            } else {
                // trying to build the object using reflection
                $reflect = new ReflectionClass($key);
                if ($reflect->isInstantiable()) {
                    $key = strtolower($key);
                    $constructor = $reflect->getConstructor();
                    if ($constructor) {
                        $parameters = $constructor->getParameters();
                        $parameters_constructor = [];
                        foreach ($parameters as $parameter) {
                            if ($parameter->getClass()) {
                                $parameters_constructor[] = $this->get($parameter->getClass()->getName());
                            } else {
                                $parameters_constructor[] = $parameter->getDefaultValue();
                            }
                        }
                        $this->instances[$key] = $reflect->newInstanceArgs($parameters_constructor);
                    } else {
                        $this->instances[$key] = $reflect->newInstance();
                    }
                } else {
                    throw new Exception('Could not resolve dependency : \'' . $key .'\' is not an instanciable Class.');
                }
            }
        }
        return $this->instances[$key];
    }
}
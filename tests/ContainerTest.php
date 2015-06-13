<?php

require 'src/Container.php';

/**
 * Class ContainerTest
 *
 * @author yach
 * @package IoC
 */
class ContainerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var
     */
    protected $container;


    /**
     *
     */
    public function setUp() {
        $this->container = new IoC\Container();
    }

    /**
     *
     */
    public function testContainerIsContainer() {
        $this->assertInstanceOf('IoC\Container', $this->container);
    }

    /**
     *
     */
    public function testReturnsNullWhenBindingNotFound() {
        $this->assertNull($this->container->getBinding('bar'));
    }

    /**
     *
     */
    public function testResolveClassReturnsObject() {
        $obj = $this->container->resolve('Foo');
        $this->assertInstanceOf('Foo', $obj);
    }

    /**
     *
     */
    public function testArrayAccessWorksAsIntended() {
        $this->container['foo'] = 'Foo';
        $this->assertInstanceOf('Foo', $this->container['foo']);
    }
}

/**
 * Class Foo
 */
class Foo {

}

/**
 * Class Bar
 */
class Bar {

    public function __construct(Foo $foo) {

    }
}
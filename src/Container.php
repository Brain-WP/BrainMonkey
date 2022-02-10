<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class Container
{
    /**
     * @var Container|null
     */
    private static $instance;

    /**
     * @var array<string, object>
     */
    private $services = [];

    /**
     * @return Container
     */
    public static function instance()
    {
        if (!self::$instance) {
            require_once dirname(__DIR__) . '/inc/patchwork-loader.php';
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @return Expectation\ExpectationFactory
     */
    public function expectationFactory()
    {
        return $this->service(
            __FUNCTION__,
            static function () {
                return new Expectation\ExpectationFactory();
            }
        );
    }

    /**
     * @return Hook\HookRunningStack
     */
    public function hookRunningStack()
    {
        return $this->service(
            __FUNCTION__,
            static function () {
                return new Hook\HookRunningStack();
            }
        );
    }

    /**
     * @return Hook\HookStorage
     */
    public function hookStorage()
    {
        return $this->service(
            __FUNCTION__,
            static function () {
                return new Hook\HookStorage();
            }
        );
    }

    /**
     * @return Hook\HookExpectationExecutor
     */
    public function hookExpectationExecutor()
    {
        return $this->service(
            __FUNCTION__,
            static function (Container $container) {
                $stack = $container->hookRunningStack();
                $factory = $container->expectationFactory();

                return new Hook\HookExpectationExecutor($stack, $factory);
            }
        );
    }

    /**
     * @return Expectation\FunctionStubFactory
     */
    public function functionStubFactory()
    {
        return $this->service(
            __FUNCTION__,
            static function () {
                return new Expectation\FunctionStubFactory();
            }
        );
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->expectationFactory()->reset();
        $this->hookRunningStack()->reset();
        $this->hookStorage()->reset();
        $this->functionStubFactory()->reset();
    }

    /**
     * @template T of object
     *
     * @param string $id
     * @param callable(Container):T $serviceFactory
     * @return T
     */
    private function service($id, callable $serviceFactory)
    {
        if (!array_key_exists($id, $this->services)) {
            $this->services[$id] = $serviceFactory($this);
        }

        /** @var T $service */
        $service = $this->services[$id];

        return $service;
    }
}

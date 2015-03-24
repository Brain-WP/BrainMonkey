<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey;

use Patchwork;
use Mockery;
use InvalidArgumentException;
use RuntimeException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class Functions
{
    /**
     * @var string Name of the function to mock
     */
    private $name;

    /**
     * Factory method: receives the name of the function to mock and return an instance of
     * FunctionMock where is possible to call any of the mocking functions.
     *
     * @param  string                  $functionName the name of the function to mock
     * @return \Brain\Monkey\Functions
     */
    public static function when($functionName)
    {
        $names = self::check($functionName);
        $name = array_pop($names);
        $namespace = empty($names) ? false : implode('\\', $names);
        $instance = new static();
        $instance->name = $namespace ? $namespace.'\\'.$name : $name;
        if (! function_exists($instance->name)) {
            eval(($namespace ? "namespace {$namespace};\n" : '')."function {$name}() {};");
        }

        return $instance;
    }

    /**
     * Returns a Mockery Expectation object, where is possible to set all the expectations, using
     * Mockery methods.
     *
     * @param  string               $functionName the name of the function to mock
     * @return \Mockery\Expectation
     * @see http://docs.mockery.io/en/latest/reference/expectations.html
     */
    public static function expect($functionName)
    {
        self::when($functionName);
        $mockery = Mockery::mock($functionName.time());
        /** @var \Mockery\ExpectationInterface $expectation */
        $expectation = $mockery->shouldReceive($functionName);
        Patchwork\replace($functionName, function () use (&$mockery, $functionName) {
            return call_user_func_array([$mockery, $functionName], func_get_args());
        });

        return new MockeryBridge($expectation);
    }

    /**
     * Checks the name of a function and throw an exception if is not valid. When name is valid
     * returns an array of the name itself and its namespace parts.
     *
     * @param  string $functionName
     * @return array
     */
    private static function check($functionName)
    {
        $names = is_string($functionName) ? explode('\\', $functionName) : false;
        $valid = function ($n) {
            return is_string($n) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $n);
        };
        if (! $names || array_filter($names, $valid) !== $names) {
            $name = $names ? $functionName : 'The value passed to '.__CLASS__;
            throw new InvalidArgumentException("'{$name}' is not a valid function name.");
        }

        return $names;
    }

    /**
     * Mocks the function and makes it return an arbitrary value.
     *
     * @param mixed $return
     */
    public function justReturn($return = null)
    {
        Patchwork\replace($this->name, function () use ($return) {
            return $return;
        });
    }

    /**
     * Mocks the function making it return one of the received arguments, the first by default.
     * Throw an exception if the function does not receive desired argument.
     *
     * @param int $n The position (1-based) of the argument to return
     */
    public function returnArg($n = 1)
    {
        $name = $this->name;
        if (! is_int($n) || $n < 1) {
            throw new InvalidArgumentException(
                "Argument number for {$this->name} must be a greater than 1 integer."
            );
        }
        Patchwork\replace($name, function () use ($n, $name) {
            $count = func_num_args();
            $n0 = $n - 1;
            if ($count < $n0) {
                throw new RuntimeException(
                    "{$name} was called with {$count} params, can't return arg ".($n)."."
                );
            }

            return func_num_args() > $n0 ? func_get_arg($n0) : null;
        });
    }

    /**
     * Mocks the function replacing it on the fly with a given callable.
     *
     * @param callable $callback
     */
    public function alias(callable $callback)
    {
        Patchwork\replace($this->name, $callback);
    }
}

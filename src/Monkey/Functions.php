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
     * @var array
     */
    private static $functions = [];

    /**
     * @var string Name of the function to mock
     */
    private $name;

    /**
     * Clean up mocked functions array.
     */
    public static function __flush()
    {
        self::$functions = [];
    }

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
            $fn = $namespace ? "namespace {$namespace};\n" : '';
            $fn .= "function {$name}() {";
            $fn .= " trigger_error('{$name} is not defined nor mocked in this test.', E_USER_ERROR);";
            $fn .= "}";
            eval($fn);
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
        if (! isset(self::$functions[$functionName])) {
            self::when($functionName);
            $mockery = Mockery::mock($functionName);
            Patchwork\redefine($functionName, function () use (&$mockery, $functionName) {
                return call_user_func_array([$mockery, $functionName], func_get_args());
            });
            self::$functions[$functionName] = $mockery;
        }
        /** @var \Mockery\MockInterface $mockery */
        $mockery = self::$functions[$functionName];
        /** @var \Mockery\ExpectationInterface $expectation */
        $expectation = $mockery->shouldReceive($functionName);

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
        Patchwork\redefine($this->name, function () use ($return) {
            return $return;
        });
    }

    /**
     * Mocks the function and makes it echo an arbitrary value.
     *
     * @param mixed $value
     */
    public function justEcho($value = null)
    {
        is_null($value) and $value = '';
        if (! is_scalar($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            throw new InvalidArgumentException(
                sprintf(
                    "Please, use a string with %s, can't echo a var of type %s.",
                    __METHOD__,
                    gettype($value)
                )
            );
        }
        $this->justReturn($value);
        $cb = $this->name;
        echo (string) $cb();
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
        $n = $this->ensureArg($n);
        Patchwork\redefine($name, function () use ($n, $name) {
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
     * Mocks the function making it echo one of the received arguments, the first by default.
     *
     * @param int $n The position (1-based) of the argument to echo
     */
    public function echoArg($n = 1)
    {
        $name = $this->name;
        $n = $this->ensureArg($n);
        Patchwork\redefine($name, function () use ($n, $name) {
            $count = func_num_args();
            $n0 = $n - 1;
            if ($count < $n0) {
                throw new RuntimeException(
                    "{$name} was called with {$count} params, can't return arg ".($n)."."
                );
            }

            $value = func_num_args() > $n0 ? func_get_arg($n0) : '';

            if (
                ! is_scalar($value)
                && ! (is_object($value) && method_exists($value, '__toString'))
            ) {
                throw new RuntimeException(
                    sprintf(
                        "%s received as argument %d a %s, can't echo it.",
                        $name,
                        $n,
                        gettype($value)
                    )
                );
            }

            echo (string) $value;
        });
    }

    /**
     * Mocks the function replacing it on the fly with a given callable.
     *
     * @param callable $callback
     */
    public function alias(callable $callback)
    {
        Patchwork\redefine($this->name, $callback);
    }

    /**
     * @param  int $n
     * @return int
     */
    private function ensureArg($n)
    {
        if (! is_int($n) || $n < 1) {
            throw new InvalidArgumentException(
                "Argument number for {$this->name} must be a greater than 1 integer."
            );
        }

        return $n;
    }
}

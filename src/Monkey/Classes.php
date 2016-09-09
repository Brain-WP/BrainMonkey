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
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class Classes
{
    /**
     * @var array
     */
    private static $classes = [];

    /**
     * @var string Fully qualified name of the method to mock, without parenthesis
     */
    private $name;

    /**
     * Clean up mocked functions array.
     */
    public static function __flush()
    {
        self::$classes = [];
    }

    /**
     * Factory method: receives the name of the class and method to mock and returns an instance of
     * Classes where is possible to call any of the mocking functions.
     *
     * @param  string                 $fullyQualifiedMethodName e.g. My\Namespace\Class::methodName
     * @return \Brain\Monkey\Classes
     */
    public static function when($fullyQualifiedMethodName)
    {
        $fullyQualifiedMethodName = self::removeTrailingParanthesis($fullyQualifiedMethodName);
        self::check($fullyQualifiedMethodName);
        $instance = new static();
        $instance->name = $fullyQualifiedMethodName;
        return $instance;
    }

    /**
     * Returns a Mockery Expectation object, where is possible to set all the expectations, using
     * Mockery methods.
     *
     * @param  string               $fullyQualifiedMethodName the name of the function to mock
     * @return \Mockery\Expectation
     * @see http://docs.mockery.io/en/latest/reference/expectations.html
     */
    public function expect($fullyQualifiedMethodName)
    {
        $fullyQualifiedMethodName = self::removeTrailingParanthesis($fullyQualifiedMethodName);
        self::check($fullyQualifiedMethodName);
        list($class, $method) = explode('::', $fullyQualifiedMethodName);

        if (! isset(self::$classes[$fullyQualifiedMethodName])) {
            self::when($fullyQualifiedMethodName);
            $mockery = Mockery::mock($class);
            Patchwork\replace($fullyQualifiedMethodName, function () use (&$mockery, $method) {
                return call_user_func_array([$mockery, $method], func_get_args());
            });
            self::$classes[$fullyQualifiedMethodName] = $mockery;
        }
        /** @var \Mockery\MockInterface $mockery */
        $mockery = self::$classes[$fullyQualifiedMethodName];
        /** @var \Mockery\ExpectationInterface $expectation */
        $expectation = $mockery->shouldReceive($method);

        return new MockeryBridge($expectation);
    }

    /**
     * Checks the fully qualified name of class method and throw an exception if is not valid.
     *
     * @param  string $fullyQualifiedMethodName
     * @throws InvalidArgumentException if the name is invalid
     */
    private static function check($fullyQualifiedMethodName)
    {
        $names = is_string($fullyQualifiedMethodName) ? explode('\\', $fullyQualifiedMethodName) : false;

        if (! $names) {
            throw new InvalidArgumentException(sprintf("The value passed to %s is not a valid class name.",__CLASS__));
        }

        $validNamespace = function ($n) {
            return is_string($n) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $n);
        };

        $canonicalMethodName = array_pop( $names );
        if (! preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*::[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $canonicalMethodName)) {
            throw new InvalidArgumentException("'$fullyQualifiedMethodName' is not a valid class name.");
        }

        if (array_filter($names, $validNamespace) !== $names) {
            throw new InvalidArgumentException("'$fullyQualifiedMethodName' is not a valid namespace.");
        }

    }


    /**
     * Removes () from the end of a fully qualified method name if they are present.
     *
     * @param  string $fullyQualifiedMethodName
     * @return string
     */
    private static function removeTrailingParanthesis($fullyQualifiedMethodName)
    {
        return preg_replace('/\(\)$/', '', $fullyQualifiedMethodName);
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
        Patchwork\replace($this->name, function () use ($value) {
            echo (string) $value;
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
        $n = $this->ensureArg($n);
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
     * Mocks the function making it echo one of the received arguments, the first by default.
     *
     * @param int $n The position (1-based) of the argument to echo
     */
    public function echoArg($n = 1)
    {
        $name = $this->name;
        $n = $this->ensureArg($n);
        Patchwork\replace($name, function () use ($n, $name) {
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
        Patchwork\replace($this->name, $callback);
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
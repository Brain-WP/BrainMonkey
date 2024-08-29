<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation;

use Brain\Monkey\Name\FunctionName;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionStub
{

    /**
     * @var \Brain\Monkey\Name\FunctionName
     */
    private $function_name;

    /**
     * @param FunctionName $function_name
     */
    public function __construct(FunctionName $function_name)
    {
        $this->function_name = $function_name;
        $name = $this->function_name->shortName();
        $namespace = $this->function_name->getNamespace();

        if (function_exists($function_name->fullyQualifiedName())) {
            return;
        }

        $function = <<<PHP
namespace {$namespace} {
    function {$name}() {
        throw new \Brain\Monkey\Expectation\Exception\MissingFunctionExpectations(
            '"{$name}" is not defined nor mocked in this test.'
        );
     }
}
PHP;
        eval($function);
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->function_name->fullyQualifiedName();
    }

    /**
     * Redefine target function replacing it on the fly with a given callable.
     *
     * @param callable $callback
     */
    public function alias(callable $callback)
    {
        $fqn = $this->function_name->fullyQualifiedName();
        \Patchwork\redefine($fqn, $callback);
        $this->assertRedefined($fqn);
    }

    /**
     * Redefine target function replacing it with a function that execute Brain Monkey expectation
     * target method on the mock associated with given Brain Monkey expectation.
     *
     * @param \Brain\Monkey\Expectation\Expectation $expectation
     * @return void
     */
    public function redefineUsingExpectation(Expectation $expectation)
    {
        $fqn = $this->function_name->fullyQualifiedName();

        $this->alias(function (...$args) use ($expectation, $fqn) {

            $mock = $expectation->mockeryExpectation()->getMock();
            $target = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, $fqn);

            return $mock->{$target->mockMethodName()}(...$args);
        });
    }

    /**
     * Redefine target function making it return an arbitrary value.
     *
     * @param mixed $return
     */
    public function justReturn($return = null)
    {
        $fqn = ltrim($this->function_name->fullyQualifiedName(), '\\');

        \Patchwork\redefine($fqn, function () use ($return) {
            return $return;
        });

        $this->assertRedefined($fqn);
    }

    /**
     * Redefine target function making it echo an arbitrary value.
     *
     * @param mixed $value
     */
    public function justEcho($value = null)
    {
        is_null($value) and $value = '';
        $fqn = ltrim($this->function_name->fullyQualifiedName(), '\\');

        $this->assertPrintable($value, 'provided to justEcho');

        \Patchwork\redefine($fqn, function () use ($value) {
            echo $value;
        });

        $this->assertRedefined($fqn);
    }

    /**
     * Redefine target function making it return one of the received arguments, the first by
     * default. Redefined function will throw an exception if the function does not receive desired
     * argument.
     *
     * @param int $arg_num The position (1-based) of the argument to return
     */
    public function returnArg($arg_num = 1)
    {
        $arg_num = $this->assertValidArgNum($arg_num, 'returnArg');

        $fqn = $this->function_name->fullyQualifiedName();

        \Patchwork\redefine($fqn, function (...$args) use ($fqn, $arg_num) {
            if ( ! array_key_exists($arg_num - 1, $args)) {
                $count = count($args);
                throw new Exception\InvalidArgumentForStub(
                    "{$fqn} was called with {$count} params, can't return argument \"{$arg_num}\"."
                );
            }

            return $args[$arg_num - 1];
        });
        $this->assertRedefined($fqn);
    }

    /**
     * Redefine target function making it echo one of the received arguments, the first by default.
     * Redefined function will throw an exception if the function does not receive desired argument.
     *
     * @param int $arg_num The position (1-based) of the argument to echo
     */
    public function echoArg($arg_num = 1)
    {
        $arg_num = $this->assertValidArgNum($arg_num, 'echoArg');

        $fqn = $this->function_name->fullyQualifiedName();

        \Patchwork\redefine($fqn, function (...$args) use ($fqn, $arg_num) {

            if ( ! array_key_exists($arg_num - 1, $args)) {
                $count = count($args);
                throw new \RuntimeException(
                    "{$fqn} was called with {$count} params, can't return argument \"{$arg_num}\"."
                );
            }

            $arg = $args[$arg_num - 1];

            $this->assertPrintable($arg, "passed as argument {$arg_num} to {$fqn}");

            echo (string)$arg;
        });

        $this->assertRedefined($fqn);
    }

    /**
     * @param mixed  $arg_num
     * @param string $method
     * @return bool
     */
    private function assertValidArgNum($arg_num, $method)
    {
        if ( ! is_int($arg_num) || $arg_num <= 0) {
            throw new Exception\InvalidArgumentForStub(
                sprintf('`%s::%s()` first parameter must be a positiver integer.', __CLASS__,
                    $method)
            );
        }

        return $arg_num;
    }

    /**
     * @param string $function_name
     */
    private function assertRedefined($function_name)
    {
        if (\Patchwork\hasMissed($function_name)) {
            throw Exception\MissedPatchworkReplace::forFunction($function_name);
        }
    }

    /**
     * @param        $value
     * @param string $coming
     */
    private function assertPrintable($value, $coming = '')
    {
        if (is_scalar($value)) {
            return;
        }

        $printable =
            is_object($value)
            && method_exists($value, '__toString')
            && is_callable([$value, '__toString']);

        if ( ! $printable) {
            throw new Exception\InvalidArgumentForStub(
                sprintf(
                    "%s, %s, is not printable.",
                    is_object($value) ? 'Instance of '.get_class($value) : gettype($value),
                    $coming
                )
            );
        }

    }
}

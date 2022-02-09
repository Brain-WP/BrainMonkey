<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation;

use Brain\Monkey\Name\FunctionName;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionStub
{
    /**
     * @var \Brain\Monkey\Name\FunctionName
     */
    private $functionName;

    /**
     * @param FunctionName $functionName
     */
    public function __construct(FunctionName $functionName)
    {
        $this->functionName = $functionName;
        $name = $this->functionName->shortName();
        $namespace = $this->functionName->getNamespace();

        if (function_exists($functionName->fullyQualifiedName())) {
            return;
        }

        $function = <<<PHP
namespace {$namespace} {
    function {$name}() {
        trigger_error(
            '"{$name}" is not defined nor mocked in this test.',
            E_USER_ERROR
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
        return $this->functionName->fullyQualifiedName();
    }

    /**
     * Redefine target function replacing it on the fly with a given callable.
     *
     * @param callable $callback
     */
    public function alias(callable $callback)
    {
        $fqn = $this->functionName->fullyQualifiedName();
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
        $fqn = $this->functionName->fullyQualifiedName();

        $this->alias(
            static function (...$args) use ($expectation, $fqn) {
                $mock = $expectation->mockeryExpectation()->getMock();
                $target = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, $fqn);

                return $mock->{$target->mockMethodName()}(...$args);
            }
        );
    }

    /**
     * Redefine target function making it return an arbitrary value.
     *
     * @param mixed $return
     */
    public function justReturn($return = null)
    {
        $fqn = ltrim($this->functionName->fullyQualifiedName(), '\\');

        \Patchwork\redefine(
            $fqn,
            static function () use ($return) {
                return $return;
            }
        );

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
        $fqn = ltrim($this->functionName->fullyQualifiedName(), '\\');

        $this->assertPrintable($value, 'provided to justEcho');

        \Patchwork\redefine(
            $fqn,
            static function () use ($value) {
                echo $value;
            }
        );

        $this->assertRedefined($fqn);
    }

    /**
     * Redefine target function making it return one of the received arguments, the first by
     * default. Redefined function will throw an exception if the function does not receive desired
     * argument.
     *
     * @param int $argNum The position (1-based) of the argument to return
     */
    public function returnArg($argNum = 1)
    {
        $argNum = $this->assertValidArgNum($argNum, 'returnArg');

        $fqn = $this->functionName->fullyQualifiedName();

        \Patchwork\redefine(
            $fqn,
            static function (...$args) use ($fqn, $argNum) {
                if (!array_key_exists($argNum - 1, $args)) {
                    $count = count($args);
                    throw new Exception\InvalidArgumentForStub(
                        "{$fqn} was called with {$count} params, "
                        . "can't return argument \"{$argNum}\"."
                    );
                }

                return $args[$argNum - 1];
            }
        );
        $this->assertRedefined($fqn);
    }

    /**
     * Redefine target function making it echo one of the received arguments, the first by default.
     * Redefined function will throw an exception if the function does not receive desired argument.
     *
     * @param int $argNum The position (1-based) of the argument to echo
     */
    public function echoArg($argNum = 1)
    {
        $argNum = $this->assertValidArgNum($argNum, 'echoArg');

        $fqn = $this->functionName->fullyQualifiedName();

        \Patchwork\redefine(
            $fqn,
            function (...$args) use ($fqn, $argNum) {
                if (!array_key_exists($argNum - 1, $args)) {
                    $count = count($args);
                    throw new \RuntimeException(
                        "{$fqn} was called with {$count} params, "
                        . "can't return argument \"{$argNum}\"."
                    );
                }

                $arg = $args[$argNum - 1];

                $this->assertPrintable($arg, "passed as argument {$argNum} to {$fqn}");

                echo (string)$arg;
            }
        );

        $this->assertRedefined($fqn);
    }

    /**
     * @param mixed $argNum
     * @param string $method
     * @return int
     */
    private function assertValidArgNum($argNum, $method)
    {
        if (!is_int($argNum) || $argNum <= 0) {
            throw new Exception\InvalidArgumentForStub(
                sprintf(
                    '`%s::%s()` first parameter must be a positiver integer.',
                    __CLASS__,
                    $method
                )
            );
        }

        return $argNum;
    }

    /**
     * @param string $functionName
     */
    private function assertRedefined($functionName)
    {
        if (\Patchwork\hasMissed($functionName)) {
            throw Exception\MissedPatchworkReplace::forFunction($functionName);
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

        if (!$printable) {
            throw new Exception\InvalidArgumentForStub(
                sprintf(
                    "%s, %s, is not printable.",
                    is_object($value) ? 'Instance of ' . get_class($value) : gettype($value),
                    $coming
                )
            );
        }
    }
}

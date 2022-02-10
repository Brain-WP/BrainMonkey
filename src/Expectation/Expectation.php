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

use Mockery;

/**
 * A wrap around Mockery expectation.
 *
 * Acts as "man in the middle" between Monkey API and Mockery expectation, preventing calls to
 * some methods and do some checks before calling other methods.
 * finally, some additional methods are added like `andAlsoExpect` to overcome the not allowed
 * `getMock()` and `andReturnFirstArg()` to facilitate the creation of expectation for applied
 * filter hooks.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @package Brain\Monkey
 *
 * @method Expectation once()
 * @method Expectation twice()
 * @method Expectation atLeast()
 * @method Expectation atMost()
 * @method Expectation times(int $times)
 * @method Expectation never()
 * @method Expectation ordered()
 * @method Expectation between(int $min, int $max)
 * @method Expectation zeroOrMoreTimes()
 * @method Expectation withAnyArgs()
 * @method Expectation andReturn(...$args)
 * @method Expectation andReturnNull()
 * @method Expectation andReturnValues(...$args)
 * @method Expectation andReturnUsing(callable $args)
 * @method Expectation andThrow(\Throwable $throwable)
 */
class Expectation
{
    const RETURNING_EXPECTATION_TYPES = [
        ExpectationTarget::TYPE_FILTER_APPLIED,
        ExpectationTarget::TYPE_FUNCTION,
    ];

    const ADDING_TYPES = [
        ExpectationTarget::TYPE_ACTION_ADDED,
        ExpectationTarget::TYPE_FILTER_ADDED,
    ];

    const REMOVING_TYPES = [
        ExpectationTarget::TYPE_ACTION_REMOVED,
        ExpectationTarget::TYPE_FILTER_REMOVED,
    ];

    const NO_ARGS_EXPECTATION_TYPES = [
        ExpectationTarget::TYPE_ACTION_DONE,
        ExpectationTarget::TYPE_FUNCTION,
    ];

    const NOT_ALLOWED_METHODS = [
        'shouldReceive',
        'andSet',
        'set',
        'shouldExpect',
        'mock',
        'getMock',
    ];

    /**
     * @var Mockery\ExpectationInterface
     */
    private $expectation;

    /**
     * @var ExpectationTarget
     */
    private $target;

    /**
     * @var bool
     */
    private $default = true;

    /**
     * @var \ArrayAccess
     */
    private $returnExpectations;

    /**
     * @param Mockery\Expectation $expectation
     * @param ExpectationTarget $target
     * @param \ArrayAccess $returnExpectations
     */
    public function __construct(
        Mockery\ExpectationInterface $expectation,
        ExpectationTarget $target,
        \ArrayAccess $returnExpectations = null
    ) {

        $this->expectation = $expectation;
        $this->target = $target;
        $this->returnExpectations = $returnExpectations ?: new \ArrayObject();
    }

    /**
     * Ensure full cloning.
     *
     * @codeCoverageIgnore
     */
    public function __clone()
    {
        $this->expectation = clone $this->expectation;
        $this->target = clone $this->target;
    }

    /**
     * Delegate method to wrapped expectation, after some checks.
     *
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public function __call($name, array $arguments = [])
    {
        if (in_array($name, self::NOT_ALLOWED_METHODS, true)) {
            throw Exception\NotAllowedMethod::forMethod($name);
        }

        $hasReturn = stristr($name, 'return');
        $hasDefault = $name === 'byDefault';

        if ($hasDefault && $this->target->type() !== ExpectationTarget::TYPE_FUNCTION) {
            throw Exception\NotAllowedMethod::forByDefault();
        }

        if (
            $hasReturn
            && !in_array($this->target->type(), self::RETURNING_EXPECTATION_TYPES, true)
        ) {
            throw Exception\NotAllowedMethod::forReturningMethod($name);
        }

        if ($this->default) {
            $this->default = false;
            $this->andAlsoExpectIt();
        }

        $callback = [$this->expectation, $name];

        $this->expectation = $callback(...$arguments);

        if ($hasReturn) {
            $id = $this->target->identifier();
            $this->returnExpectations->offsetExists($id) or $this->returnExpectations[$id] = 1;
        }

        return $this;
    }

    /**
     * @return Mockery\ExpectationInterface
     */
    public function mockeryExpectation()
    {
        return $this->expectation;
    }

    /**
     * Mockery expectations allow chaining different expectations by chaining `getMock()` method.
     * Since `getMock()` is disabled for Brain Monkey expectations, this method provides a way to
     * chain expectations.
     *
     * @return static
     */
    public function andAlsoExpectIt()
    {
        $method = $this->target->mockMethodName();
        /** @var Mockery\ExpectationInterface $expectation */
        $expectation = $this->expectation->getMock()->shouldReceive($method);
        $this->expectation = $expectation;

        return $this;
    }

    /**
     * WordPress action and filters addition and filters applying requires at least one argument,
     * and setting an expectation of no arguments for those triggers an error in Brain Monkey.
     *
     * @return static
     */
    public function withNoArgs()
    {
        if (!in_array($this->target->type(), self::NO_ARGS_EXPECTATION_TYPES, true)) {
            throw Exception\ExpectationArgsRequired::forExpectationType($this->target);
        }

        /** @var Mockery\Expectation $expectation */
        $expectation = $this->expectation;
        $this->expectation = $expectation->withNoArgs();

        return $this;
    }

    /**
     * @param mixed ...$args
     * @return static
     */
    public function with(...$args)
    {
        $argsNum = count($args);

        if (
            !$argsNum
            && !in_array($this->target->type(), self::NO_ARGS_EXPECTATION_TYPES, true)
        ) {
            throw Exception\ExpectationArgsRequired::forExpectationType($this->target);
        }

        if (in_array($this->target->type(), self::ADDING_TYPES, true) && $argsNum < 3) {
            $argsNum < 2 and $args[] = 10;
            $args[] = 1;
        }

        if (in_array($this->target->type(), self::REMOVING_TYPES, true) && $argsNum === 1) {
            $args[] = 10;
        }

        /** @var Mockery\Expectation $expectation */
        $expectation = $this->expectation;
        $this->expectation = $expectation->with(...$args);

        return $this;
    }

    /**
     * Brain Monkey doesn't allow return expectation for actions (added/done) nor for added filters.
     * However, it is desirable to do something when the expected callback is used, this is the
     * reason to be of this method.
     *
     * ```
     * Actions::expectDone('some_action')->once()->whenHappen(function($some_arg) {
     *      echo "{$some_arg} was passed to " . current_filter();
     * });
     * ```
     *
     * Snippet above will not change the return of `do_action('some_action', $some_arg)` like a
     * normal return expectation would do, but allows catching expected events with a callback.
     *
     * For expectation types that allows return expectation (functions, applied filters) this method
     * becomes just an alias for Mockery `andReturnUsing()`.
     *
     * @param callable $callback
     * @return static
     */
    public function whenHappen(callable $callback)
    {
        if (in_array($this->target->type(), self::RETURNING_EXPECTATION_TYPES, true)) {
            throw Exception\NotAllowedMethod::forWhenHappen($this->target);
        }
        /** @var Mockery\Expectation $expectation */
        $expectation = $this->expectation;
        $expectation->andReturnUsing($callback);

        return $this;
    }

    /**
     * @return static
     */
    public function andReturnFirstArg()
    {
        if (!in_array($this->target->type(), self::RETURNING_EXPECTATION_TYPES, true)) {
            throw Exception\NotAllowedMethod::forReturningMethod('andReturnFirstParam');
        }

        /** @var Mockery\Expectation $expectation */
        $expectation = $this->expectation;
        $expectation->andReturnUsing(
            /**
             * @param mixed $arg
             * @return mixed
             */
            static function ($arg = null) {
                return $arg;
            }
        );

        return $this;
    }
}

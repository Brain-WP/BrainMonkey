<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation;

use Mockery\ExpectationInterface;

/**
 * A wrap around Mockery expectation.
 *
 * Acts as "man in the middle" between Monkey API and Mockery expectation, preventing calls to
 * some methods and do some checks before calling other methods.
 * finally, some additional methods are added like `andAlsoExpect` to overcome the not allowed
 * `getMock()` and `andReturnFirstArg()` to facilitate the creation of expectation for applied
 * filter hooks.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
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
 * @method Expectation andReturnUsing(callable ...$args)
 * @method Expectation andThrow(\Throwable $throwable)
 */
class Expectation
{

    const RETURNING_EXPECTATION_TYPES = [
        ExpectationTarget::TYPE_FILTER_APPLIED,
        ExpectationTarget::TYPE_FUNCTION
    ];

    const ADDING_TYPES = [
        ExpectationTarget::TYPE_ACTION_ADDED,
        ExpectationTarget::TYPE_FILTER_ADDED
    ];

    const REMOVING_TYPES = [
        ExpectationTarget::TYPE_ACTION_REMOVED,
        ExpectationTarget::TYPE_FILTER_REMOVED
    ];

    const NO_ARGS_EXPECTATION_TYPES = [
        ExpectationTarget::TYPE_ACTION_DONE,
        ExpectationTarget::TYPE_FUNCTION
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
     * @var \Mockery\Expectation|\Mockery\ExpectationInterface
     */
    private $expectation;

    /**
     * @var \Brain\Monkey\Expectation\ExpectationTarget
     */
    private $target;

    /**
     * @var bool
     */
    private $default = true;

    /**
     * @var \ArrayAccess
     */
    private $return_expectations;

    /**
     * @param \Mockery\ExpectationInterface               $expectation
     * @param \Brain\Monkey\Expectation\ExpectationTarget $target
     * @param \ArrayAccess|null                           $return_expectations
     */
    public function __construct(
        ExpectationInterface $expectation,
        ExpectationTarget $target,
        $return_expectations = null
    ) {
        $this->expectation = $expectation;
        $this->target = $target;
        $this->return_expectations = ($return_expectations instanceof \ArrayAccess) ? $return_expectations : new \ArrayObject();
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
     * @param array  $arguments
     * @return static
     */
    public function __call($name, array $arguments = [])
    {
        if (in_array($name, self::NOT_ALLOWED_METHODS, true)) {
            throw Exception\NotAllowedMethod::forMethod($name);
        }

        $has_return = stristr($name, 'return');
        $has_default = $name === 'byDefault';

        if ($has_default && $this->target->type() !== ExpectationTarget::TYPE_FUNCTION) {
            throw Exception\NotAllowedMethod::forByDefault();
        }

        if (
            $has_return
            && ! in_array($this->target->type(), self::RETURNING_EXPECTATION_TYPES, true)
        ) {
            throw Exception\NotAllowedMethod::forReturningMethod($name);
        }

        if ($this->default) {
            $this->default = false;
            $this->andAlsoExpectIt();
        }

        $callback = [$this->expectation, $name];

        $this->expectation = $callback(...$arguments);

        if ($has_return) {
            $id = $this->target->identifier();
            $this->return_expectations->offsetExists($id) or $this->return_expectations[$id] = 1;
        }

        return $this;
    }

    /**
     * @return \Mockery\Expectation|\Mockery\CompositeExpectation
     */
    public function mockeryExpectation()
    {
        return $this->expectation;
    }

    /**
     * Mockery expectation allow chaining different expectations with by chaining `getMock()`
     * method.
     * Since `getMock()` is disabled for Brain Monkey expectation this methods provides a way to
     * chain expectations.
     *
     * @return static
     */
    public function andAlsoExpectIt()
    {
        $method = $this->target->mockMethodName();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->expectation = $this->expectation->getMock()->shouldReceive($method);

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
        if ( ! in_array($this->target->type(), self::NO_ARGS_EXPECTATION_TYPES, true)) {
            throw Exception\ExpectationArgsRequired::forExpectationType($this->target);
        }

        $this->expectation = $this->expectation->withNoArgs();

        return $this;
    }

    /**
     * @param mixed ...$args
     * @return static
     */
    public function with(...$args)
    {
        $argsNum = count($args);

        if ( ! $argsNum &&
            ! in_array($this->target->type(), self::NO_ARGS_EXPECTATION_TYPES, true)
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

        $this->expectation = $this->expectation->with(...$args);

        return $this;
    }

    /**
     * Brain Monkey doesn't allow return expectation for actions (added/done) nor for added
     * filters.
     * However, it is desirable to do something when the expected callback is used, this is the
     * reason to be of this method.
     *
     * ```
     * Actions::expectDone('some_action')->once()->whenHappen(function($some_arg) {
     *      echo "{$some_arg} was passed to " . current_filter();
     * });
     * ```
     *
     * Snippet above will not change the return of `do_action('some_action', $some_arg)`
     * like a normal return expectation would do, but allows to catch expected events with a
     * callback.
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

        $this->expectation->andReturnUsing($callback);

        return $this;
    }

    /**
     * @return static
     */
    public function andReturnFirstArg()
    {
        if ( ! in_array($this->target->type(), self::RETURNING_EXPECTATION_TYPES, true)) {
            throw Exception\NotAllowedMethod::forReturningMethod('andReturnFirstParam');
        }

        $this->expectation->andReturnUsing(function ($arg = null) {
            return $arg;
        });

        return $this;
    }
}

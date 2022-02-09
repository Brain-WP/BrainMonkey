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

/**
 * A factory to create expectation objects for different "targets".
 *
 * It is a collection of factory methods with explicit names, that internally do always same thing.
 *
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExpectationFactory
{
    /**
     * @var \Brain\Monkey\Expectation\Expectation[]
     */
    private $expectations = [];

    /**
     * @var \ArrayObject
     */
    private $returnExpectations;

    /**
     */
    public function __construct()
    {
        $this->returnExpectations = new \ArrayObject();
    }

    /**
     * @param string $function
     * @return \Brain\Monkey\Expectation\Expectation;
     */
    public function forFunctionExecuted($function)
    {
        return $this->create(
            new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, $function)
        );
    }

    /**
     * @param string $action
     * @return \Brain\Monkey\Expectation\Expectation;
     */
    public function forActionAdded($action)
    {
        return $this->create(
            new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, $action)
        );
    }

    /**
     * @param string $action
     * @return \Brain\Monkey\Expectation\Expectation;
     */
    public function forActionDone($action)
    {
        return $this->create(
            new ExpectationTarget(ExpectationTarget::TYPE_ACTION_DONE, $action)
        );
    }

    /**
     * @param string $action
     * @return \Brain\Monkey\Expectation\Expectation;
     */
    public function forActionRemoved($action)
    {
        return $this->create(
            new ExpectationTarget(ExpectationTarget::TYPE_ACTION_REMOVED, $action)
        );
    }

    /**
     * @param string $filter
     * @return \Brain\Monkey\Expectation\Expectation;
     */
    public function forFilterAdded($filter)
    {
        return $this->create(
            new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, $filter)
        );
    }

    /**
     * @param string $filter
     * @return \Brain\Monkey\Expectation\Expectation;
     */
    public function forFilterApplied($filter)
    {
        return $this->create(
            new ExpectationTarget(ExpectationTarget::TYPE_FILTER_APPLIED, $filter)
        );
    }

    /**
     * @param string $filter
     * @return \Brain\Monkey\Expectation\Expectation;
     */
    public function forFilterRemoved($filter)
    {
        return $this->create(
            new ExpectationTarget(ExpectationTarget::TYPE_FILTER_REMOVED, $filter)
        );
    }

    /**
     * @param \Brain\Monkey\Expectation\ExpectationTarget $target
     * @return bool
     */
    public function hasMockFor(ExpectationTarget $target)
    {
        return array_key_exists($target->identifier(), $this->expectations);
    }

    /**
     * @param \Brain\Monkey\Expectation\ExpectationTarget $target
     * @return bool
     */
    public function hasReturnExpectationFor(ExpectationTarget $target)
    {
        if (!$this->hasMockFor($target)) {
            return false;
        }

        return $this->returnExpectations->offsetExists($target->identifier());
    }

    /**
     * @param \Brain\Monkey\Expectation\ExpectationTarget $target
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|null
     */
    public function mockFor(ExpectationTarget $target)
    {
        return $this->hasMockFor($target)
            ? $this->expectations[$target->identifier()]->mockeryExpectation()->getMock()
            : \Mockery::mock();
    }

    public function reset()
    {
        $this->expectations = [];
        $this->returnExpectations = new \ArrayObject();
    }

    /**
     * @param \Brain\Monkey\Expectation\ExpectationTarget $target
     * @return \Brain\Monkey\Expectation\Expectation
     */
    private function create(ExpectationTarget $target)
    {
        $id = $target->identifier();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $expectation = $this->mockFor($target)
            ->shouldReceive($target->mockMethodName())
            ->atLeast()
            ->once();

        if ($target->type() === ExpectationTarget::TYPE_FILTER_APPLIED) {
            $expectation = $expectation->andReturnUsing(
                static function ($arg) {
                    return $arg;
                }
            );
        }

        $expectation = $expectation->byDefault();

        $this->expectations[$id] = new Expectation(
            $expectation,
            $target,
            $this->returnExpectations
        );

        return $this->expectations[$id];
    }
}

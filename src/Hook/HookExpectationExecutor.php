<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Hook;

use Brain\Monkey\Expectation\Expectation;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Expectation\ExpectationFactory;

/**
 * Class responsible to execute the mocked hook methods on the mock object.
 *
 * Expected methods that are not executed will cause tests to fail.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class HookExpectationExecutor
{

    /**
     * @var \Brain\Monkey\Hook\HookRunningStack
     */
    private $stack;

    /**
     * @var \Brain\Monkey\Expectation\ExpectationFactory
     */
    private $factory;

    /**
     * @param \Brain\Monkey\Hook\HookRunningStack          $stack
     * @param \Brain\Monkey\Expectation\ExpectationFactory $factory
     */
    public function __construct(HookRunningStack $stack, ExpectationFactory $factory)
    {
        $this->stack = $stack;
        $this->factory = $factory;
    }

    /**
     * @param string $action
     * @param array  $args
     */
    public function executeAddAction($action, array $args)
    {
        $this->execute(ExpectationTarget::TYPE_ACTION_ADDED, $action, $args);
    }

    /**
     * @param string $action
     * @param array  $args
     */
    public function executeAddFilter($action, array $args)
    {
        $this->execute(ExpectationTarget::TYPE_FILTER_ADDED, $action, $args);
    }

    /**
     * @param string $action
     * @param array  $args
     */
    public function executeDoAction($action, array $args = [])
    {
        $is_running = $this->stack->has();
        $this->stack->push($action);
        $this->execute(ExpectationTarget::TYPE_ACTION_DONE, $action, $args);
        $is_running or $this->stack->reset();
    }

    /**
     * @param string $filter
     * @param array  $args
     * @return mixed|null
     */
    public function executeApplyFilters($filter, array $args)
    {

        $is_running = $this->stack->has();
        $this->stack->push($filter);
        $return = $this->execute(ExpectationTarget::TYPE_FILTER_APPLIED, $filter, $args);
        $is_running or $this->stack->reset();

        return $return;
    }

    /**
     * @param string $action
     * @param array  $args
     * @return mixed
     */
    public function executeRemoveAction($action, array $args)
    {
        return $this->execute(ExpectationTarget::TYPE_ACTION_REMOVED, $action, $args);
    }

    /**
     * @param string $filter
     * @param array  $args
     * @return mixed
     */
    public function executeRemoveFilter($filter, array $args)
    {
        return $this->execute(ExpectationTarget::TYPE_FILTER_REMOVED, $filter, $args);
    }

    /**
     * @param string $type
     * @param string $hook
     * @param array  $args
     * @return mixed
     */
    private function execute($type, $hook, array $args)
    {
        $target = new ExpectationTarget($type, $hook);
        if ($this->factory->hasMockFor($target)) {
            $method = $target->mockMethodName();

            $return = $this->factory->mockFor($target)->{$method}(...$args);
            $this->factory->hasReturnExpectationFor($target) or $return = reset($args);

            return $return;
        }

        if ($type === ExpectationTarget::TYPE_FILTER_APPLIED) {
            return reset($args);
        }

        return null;

    }
}

<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\WP;

use LogicException;
use RuntimeException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 * @method \Mockery\Expectation|ActionExpectation times()
 * @method \Mockery\Expectation|ActionExpectation once()
 * @method \Mockery\Expectation|ActionExpectation twice()
 * @method \Mockery\Expectation|ActionExpectation zeroOrMoreTimes()
 * @method \Mockery\Expectation|ActionExpectation never()
 * @method \Mockery\Expectation|ActionExpectation atLeast()
 * @method \Mockery\Expectation|ActionExpectation atMost()
 * @method \Mockery\Expectation|ActionExpectation with()
 * @method \Mockery\Expectation|ActionExpectation withAnyArgs()
 * @method \Mockery\Expectation|ActionExpectation withNoArgs()
 * @method \Mockery\Expectation|ActionExpectation andThrow()
 * @method \Mockery\Expectation|ActionExpectation between()
 */
class ActionExpectation
{
    /**
     * @var \Mockery\Expectation
     */
    private $expectation;

    /**
     * @var bool
     */
    private $run;

    public function __construct($expectation, $run = true)
    {
        $this->expectation = $expectation;
        $this->run = $run;
    }

    public function __call($name, array $arguments = [])
    {
        if (strstr(strtolower($name), 'return') || $name === 'passthru') {
            $msg = $this->run
                ? "Don't use return expectations on actions, use whenHappen() instead."
                : "Don't use return nor whenHappen() expectation on added actions.";
            throw new LogicException($msg);
        }
        $this->expectation = call_user_func_array([$this->expectation, $name], $arguments);

        return $this;
    }

    public function whenHappen(callable $callback)
    {
        if (! $this->run) {
            throw new RuntimeException("Don't use whenHappen() expectations on added actions.");
        }

        return $this->expectation->andReturnUsing($callback);
    }
}

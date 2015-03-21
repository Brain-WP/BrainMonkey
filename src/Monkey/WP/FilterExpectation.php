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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 *
 * @method \Mockery\Expectation|FilterExpectation times()
 * @method \Mockery\Expectation|FilterExpectation once()
 * @method \Mockery\Expectation|FilterExpectation twice()
 * @method \Mockery\Expectation|FilterExpectation zeroOrMoreTimes()
 * @method \Mockery\Expectation|FilterExpectation never()
 * @method \Mockery\Expectation|FilterExpectation atLeast()
 * @method \Mockery\Expectation|FilterExpectation atMost()
 * @method \Mockery\Expectation|FilterExpectation with()
 * @method \Mockery\Expectation|FilterExpectation withAnyArgs()
 * @method \Mockery\Expectation|FilterExpectation withNoArgs()
 * @method \Mockery\Expectation|FilterExpectation andThrow()
 * @method \Mockery\Expectation|FilterExpectation between()
 */
class FilterExpectation
{
    /**
     * @var \Mockery\ExpectationInterface
     */
    private $expectation;

    public function __construct($expectation)
    {
        $this->expectation = $expectation;
    }

    public function __call($name, array $arguments = [])
    {
        if ((strstr(strtolower($name), 'return') || $name === 'passthru')) {
            throw new LogicException("Don't use return expectations on added filters.");
        }
        $this->expectation = call_user_func_array([$this->expectation, $name], $arguments);

        return $this;
    }
}

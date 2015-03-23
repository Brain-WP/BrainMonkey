<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey;

use BadMethodCallException;
use Mockery\ExpectationInterface;
use RuntimeException;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 *
 * @method MockeryBridge once()
 * @method MockeryBridge twice()
 * @method MockeryBridge times()
 * @method MockeryBridge atLeast()
 * @method MockeryBridge atMost()
 * @method MockeryBridge between()
 * @method MockeryBridge zeroOrMoreTimes()
 * @method MockeryBridge never()
 * @method MockeryBridge with()
 * @method MockeryBridge withNoArgs()
 * @method MockeryBridge withAnyArgs()
 * @method MockeryBridge andReturn()
 * @method MockeryBridge andReturnNull()
 * @method MockeryBridge andReturnValues()
 * @method MockeryBridge andReturnUsing()
 * @method MockeryBridge andThrow()
 */
class MockeryBridge
{
    /**
     * @var \Mockery\Expectation
     */
    private $expectation;

    /**
     * @var string
     */
    private $parent;

    /**
     * @var bool
     */
    private $parentCanReturn;

    /**
     * Constructor.
     *
     * @param \Mockery\ExpectationInterface $expectation
     * @param string|null                   $parent
     * @param bool                          $parentCanReturn
     */
    public function __construct(
        ExpectationInterface $expectation,
        $parent = null,
        $parentCanReturn = true
    ) {
        $this->expectation = $expectation;
        (is_string($parent) && class_exists($parent)) and $this->parent = $parent;
        $this->parentCanReturn = ! empty($parentCanReturn);
    }

    /**
     * shouldReceive() can't be called on Mockery expectation object or everything will break.
     *
     * @param  string                      $name
     * @param  array                       $arguments
     * @return \Brain\Monkey\MockeryBridge $this
     */
    public function __call($name, array $arguments = [])
    {
        $notAllowed = ['shouldreceive', 'andset', 'shouldexpect'];
        if (in_array(strtolower($name), $notAllowed, true)) {
            throw new BadMethodCallException(
                "shouldReceive(), shouldExpect() and andSet() methods are not allowed in Brain Monkey."
            );
        }
        if (strstr(strtolower($name), 'return') && ! empty($this->parent)) {
            $this->checkReturn();
        }
        $this->expectation = call_user_func_array([$this->expectation, $name], $arguments);

        return $this;
    }

    /**
     * WordPress testing function, ignored for generic PHP function testing.
     * Used to avoid return expectations on action hooks.
     *
     * @param  callable             $callback
     * @return \Mockery\Expectation
     */
    public function whenHappen(callable $callback)
    {
        if ($this->parent !== 'Brain\Monkey\WP\Actions') {
            throw new RuntimeException('whenHappen() can only be used for WordPress action expectations.');
        }
        if (! $this->parentCanReturn) {
            throw new RuntimeException("Don't use whenHappen() expectations on added actions.");
        }

        return $this->expectation->andReturnUsing($callback);
    }

    /**
     * WordPress testing function, ignored for generic PHP function testing.
     * Used to avoid return expectations on added hooks.
     */
    private function checkReturn()
    {
        if (! $this->parentCanReturn) {
            throw new LogicException("Don't use return expectations on added hooks.");
        }
        if ($this->parent === 'Brain\Monkey\WP\Actions') {
            throw new LogicException("Don't use return expectations on actions, use whenHappen() instead.");
        }
    }
}

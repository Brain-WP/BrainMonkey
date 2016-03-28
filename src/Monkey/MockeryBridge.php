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
     * @var bool
     */
    private $isHook = false;

    /**
     * @var bool
     */
    private $isAction = false;

    /**
     * Constructor.
     *
     * @param \Mockery\ExpectationInterface $expectation
     * @param string|null                   $parent
     */
    public function __construct(ExpectationInterface $expectation, $parent = null)
    {
        $this->expectation = $expectation;
        $name = $this->expectation->__toString();
        if (is_string($parent) && class_exists($parent)) {
            $parent = trim($parent, '\\');
            $reflection = new \ReflectionClass($parent);
            $this->isHook = $reflection->isSubclassOf('Brain\Monkey\WP\Hooks');
            $this->isAction =
                $parent === 'Brain\Monkey\WP\Actions'
                || is_subclass_of($parent, 'Brain\Monkey\WP\Actions');
        }
        $this->isAddedHook = $this->isHook && strpos($name, '[add_') === 0;
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
        if (stristr($name, 'return') && $this->isHook) {
            $this->checkReturn($name);
        }
        $this->expectation = call_user_func_array([$this->expectation, $name], $arguments);

        return $this;
    }

    /**
     * WordPress testing function used to avoid return expectations on action hooks.
     * Throws exception for WordPress filters and generic PHP function testing.
     *
     * @param  callable             $callback
     * @return \Mockery\Expectation
     */
    public function whenHappen(callable $callback)
    {
        if (! $this->isAddedHook && ! $this->isAction) {
            throw new RuntimeException(
                'whenHappen() can only be used for WordPress actions or added filters expectations.'
            );
        }

        return $this->expectation->andReturnUsing($callback);
    }

    /**
     * WordPress testing function, ignored for generic PHP function testing.
     * Used to avoid return expectations on added hooks.
     *
     * @param string $name
     */
    private function checkReturn($name)
    {
        if ($this->isAction) {
            throw new LogicException(
                "Don't use return expectations on actions, use whenHappen() instead."
            );
        }
        if (strpos($name, 'add_') === 0) {
            throw new LogicException(
                "Don't use return expectations on added hooks."
            );
        }
    }
}

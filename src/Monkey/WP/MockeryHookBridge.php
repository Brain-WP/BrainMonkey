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

use Brain\Monkey\MockeryBridge;
use Mockery;

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
 * @method Mockery\Expectation whenHappen()
 */
class MockeryHookBridge
{
    /**
     * @var \Brain\Monkey\MockeryBridge
     */
    private $bridge;

    public function __construct(MockeryBridge $bridge)
    {
        $this->bridge = $bridge;
    }

    public function __call($name, array $arguments = [])
    {
        return call_user_func_array([$this->bridge, $name], $arguments);
    }
}

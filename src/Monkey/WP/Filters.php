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
use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class Filters extends Hooks
{
    /**
     * @param  string                             $filter
     * @return \Brain\Monkey\WP\MockeryHookBridge
     */
    public static function expectApplied($filter)
    {
        return self::createBridgeFor(self::FILTER, $filter, 'run');
    }

    /**
     * @param  string                             $filter
     * @return \Brain\Monkey\WP\MockeryHookBridge
     */
    public static function expectAdded($filter)
    {
        return self::createBridgeFor(self::FILTER, $filter, 'add');
    }

    /**
     * @inheritdoc
     */
    public function add()
    {
        $args = func_get_args();
        array_unshift($args, self::FILTER);

        return call_user_func_array([$this, 'addHook'], $args);
    }

    /**
     * @inheritdoc
     */
    public function remove()
    {
        $args = func_get_args();
        array_unshift($args, self::FILTER);

        return call_user_func_array([$this, 'removeHook'], $args);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $args = func_get_args();
        array_unshift($args, self::FILTER);

        return call_user_func_array([$this, 'runHook'], $args);
    }

    /**
     * @inheritdoc
     */
    public function runRef()
    {
        if (func_num_args() < 2 || ! is_array(func_get_arg(1))) {
            throw new LogicException('apply_filters_ref_array() needs an array as second argument.');
        }
        $args = func_get_arg(1);
        array_unshift($args, func_get_arg(0));

        return call_user_func_array([$this, 'run'], $args);
    }

    /**
     * @inheritdoc
     */
    public function has()
    {
        $args = func_get_args();
        array_unshift($args, self::FILTER);

        return call_user_func_array([$this, 'hasHook'], $args);
    }

    /**
     * Checks if a specific action has been triggered.
     *
     * @param  string $filter
     * @return int
     */
    public function applied($filter)
    {
        if (empty($filter) || ! is_string($filter)) {
            throw new InvalidArgumentException("Action name must be in a string.");
        }

        return in_array($filter, $this->done, true) ? array_count_values($this->done)[$filter] : 0;
    }

    /**
     * @inheritdoc
     */
    public function clean()
    {
        $this->cleanInstance($this);
    }
}

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

/**
 * A simple stack data structure built around an array for hook names.
 * This allow to keep last hook being executed.
 *
 * It is used to `current_filter()`, `doing_action()`, and `doing_filter()`.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class HookRunningStack
{

    /**
     * @var array
     */
    private $stack = [];

    /**
     * @param string $hook_name
     * @return static
     */
    public function push($hook_name)
    {
        $this->stack[] = $hook_name;

        return $this;
    }

    /**
     * @return string
     */
    public function last()
    {
        if ( ! $this->stack) {
            return '';
        }

        return end($this->stack);
    }

    /**
     * @param string $hook_name
     * @return bool
     */
    public function has($hook_name = null)
    {
        if ( ! $this->stack) {
            return false;
        }

        return $hook_name === null ? true : in_array($hook_name, $this->stack, true);
    }

    /**
     * @return static
     */
    public function reset()
    {
        $this->stack = [];

        return $this;
    }
}
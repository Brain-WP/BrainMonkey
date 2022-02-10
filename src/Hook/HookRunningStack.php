<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Hook;

/**
 * A simple stack data structure built around an array for hook names.
 * This allows us to keep last hook being executed.
 *
 * It is used to `current_filter()`, `doing_action()`, and `doing_filter()`.
 *
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class HookRunningStack
{
    /**
     * @var list<string>
     */
    private $stack = [];

    /**
     * @param string $hookName
     * @return static
     */
    public function push($hookName)
    {
        assert(is_string($hookName));

        $this->stack[] = $hookName;

        return $this;
    }

    /**
     * @return string
     */
    public function last()
    {
        if (!$this->stack) {
            return '';
        }

        return end($this->stack);
    }

    /**
     * @param string $hookName
     * @return bool
     */
    public function has($hookName = null)
    {
        $isNull = $hookName === null;
        assert($isNull || is_string($hookName));

        return $this->stack && ($isNull || in_array($hookName, $this->stack, true));
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

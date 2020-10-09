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

use Brain\Monkey\Name\CallbackStringForm;

/**
 * A simple stack data structure built around two arrays that maps hook names to the arguments
 * used to add or execute them.
 *
 * It is used to allow testing for hook being added/removed/executed also checking for the arguments
 * used.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class HookStorage
{

    const ACTIONS = 'actions';
    const FILTERS = 'filters';
    const ADDED   = 'added';
    const DONE    = 'done';

    private $storage = [
        self::ADDED => [],
        self::DONE  => []
    ];

    /**
     * @return void
     */
    public function reset()
    {
        $this->storage = [
            self::ADDED => [],
            self::DONE  => []
        ];
    }

    /**
     * @param string $type
     * @param string $hook
     * @param array  $args
     * @return static
     */
    public function pushToAdded($type, $hook, array $args)
    {
        return $this->pushToStorage(self::ADDED, $type, $hook, $args);
    }

    /**
     * @param string $type
     * @param string $hook
     * @param array  $args
     * @return bool
     */
    public function removeFromAdded($type, $hook, array $args)
    {
        if ( ! $this->isHookAdded($type, $hook)) {
            return false;
        }

        if ( ! $args) {
            unset($this->storage[self::ADDED][$type][$hook]);

            return true;
        }

        $args = $this->parseArgsToAdd($args, self::ADDED, $type);

        $all = $this->storage[self::ADDED][$type][$hook];
        $removed = 0;

        /**
         * @var CallbackStringForm $callback
         */
        foreach ($all as $key => list($callback, $priority)) {
            if ($callback->equals($args[0]) && $priority === $args[1]) {
                unset($all[$key]);
                $removed++;
            }
        }

        $removed and $this->storage[self::ADDED][$type][$hook] = array_values($all);
        if ( ! $this->storage[self::ADDED][$type][$hook]) {
            unset($this->storage[self::ADDED][$type][$hook]);
        }

        return $removed > 0;
    }

    /**
     * @param string $type
     * @param string $hook
     * @param array  $args
     * @return static
     */
    public function pushToDone($type, $hook, array $args)
    {
        return $this->pushToStorage(self::DONE, $type, $hook, $args);
    }

    /**
     * @param string        $type
     * @param string        $hook
     * @param callable|null $function
     * @return bool
     */
    public function isHookAdded($type, $hook, $function = null)
    {
        return $this->isInStorage(self::ADDED, $type, $hook, $function);
    }

    /**
     * @param string $type
     * @param string $hook
     * @return int
     */
    public function isHookDone($type, $hook)
    {
        return $this->isInStorage(self::DONE, $type, $hook);
    }

    /**
     * @param $type
     * @param $hook
     * @param $function
     * @return bool|int
     */
    public function hookPriority($type, $hook, $function)
    {
        if ( ! isset($this->storage[self::ADDED][$type][$hook])) {
            return false;
        }

        $all = $this->storage[self::ADDED][$type][$hook];

        /**
         * @var CallbackStringForm $callback
         * @var int                $priority
         */
        foreach ($all as $key => list($callback, $priority)) {
            if ($callback->equals(new CallbackStringForm($function))) {
                return $priority;
            }
        }

        return false;
    }

    /**
     * @param string $key
     * @param string $type
     * @param string $hook
     * @param array  $args
     * @return static
     */
    private function pushToStorage($key, $type, $hook, array $args)
    {
        if ($type !== self::ACTIONS && $type !== self::FILTERS) {
            throw Exception\InvalidHookArgument::forInvalidType($type);
        }

        if ( ! is_string($hook)) {
            throw Exception\InvalidHookArgument::forInvalidHook($hook);
        }

        // do_action() is the only of target functions that can be called without additional arguments
        if ( ! $args && ($key !== self::DONE || $type !== self::ACTIONS)) {
            throw Exception\InvalidHookArgument::forEmptyArguments($key, $type);
        }

        $storage = &$this->storage[$key];

        array_key_exists($type, $storage) or $storage[$type] = [];
        array_key_exists($hook, $storage[$type]) or $storage[$type][$hook] = [];

        if ($key === self::ADDED) {
            $args = $this->parseArgsToAdd($args, $key, $type);
        }

        $storage[$type][$hook][] = $args;

        return $this;
    }

    /**
     * @param string        $key
     * @param string        $type
     * @param string        $hook
     * @param callable|null $function
     * @return int|bool
     */
    private function isInStorage($key, $type, $hook, $function = null)
    {
        $storage = $this->storage[$key];

        if ( ! in_array($type, [self::ACTIONS, self::FILTERS], true)) {
            throw Exception\InvalidHookArgument::forInvalidType($type);
        }

        if ( ! array_key_exists($type, $storage) || ! array_key_exists($hook, $storage[$type])) {
            return $key === self::ADDED ? false : 0;
        }

        if ($function === null) {
            return $key === self::ADDED ? true : count($storage[$type][$hook]);
        }

        $filter = function (array $args) use ($function) {
            return $args[0]->equals(new CallbackStringForm($function));
        };

        $matching = array_filter($storage[$type][$hook], $filter);

        return $key === self::ADDED ? (bool)$matching : count($matching);
    }

    /**
     * @param array  $args
     * @param string $key
     * @param string $type
     * @return array
     */
    private function parseArgsToAdd(array $args, $key, $type)
    {
        if ( ! $args) {
            throw Exception\InvalidHookArgument::forEmptyArguments($key, $type);
        }

        if (count($args) > 3) {
            throw Exception\InvalidAddedHookArgument::forWrongArgumentsCount($type);
        }

        $args = array_replace([null, 10, 1], array_values($args));

        if ( ! $args[0]) {
            throw Exception\InvalidAddedHookArgument::forMissingCallback($type);
        }

        $args[0] = new CallbackStringForm($args[0]);

        if ( ! is_int($args[1])) {
            throw Exception\InvalidAddedHookArgument::forInvalidPriority($type);
        }

        if ( ! is_int($args[2])) {
            throw Exception\InvalidAddedHookArgument::forInvalidAcceptedArgs($type);
        }

        return $args;
    }
}

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

use Brain\Monkey\Name\CallbackStringForm;

/**
 * A simple stack data structure built around two arrays that maps hook names to the arguments
 * used to add or execute them.
 *
 * It is used to enable testing for hook being added/removed/executed also checking for the used
 * arguments.
 *
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @psalm-type hook-type = HookStorage::ACTIONS|HookStorage::FILTERS
 * @psalm-type hook-name = string
 * @psalm-type added-hook-args = array{CallbackStringForm, int, int}
 * @psalm-type added-hooks = array<hook-type, array<hook-name, list<added-hook-args>>>
 * @psalm-type done-hooks = array<hook-type, array<hook-name, list<array>>>
 */
final class HookStorage
{
    const ACTIONS = 'actions';
    const FILTERS = 'filters';
    const ADDED = 'added';
    const DONE = 'done';

    /**
     * @var array<HookStorage::ADDED, added-hooks>|array<HookStorage::DONE, done-hooks>
     */
    private $storage = [self::ADDED => [], self::DONE => []];

    /**
     * @return void
     */
    public function reset()
    {
        $this->storage = [self::ADDED => [], self::DONE => []];
    }

    /**
     * @param hook-type $type
     * @param hook-name $hook
     * @param array $args
     * @return static
     */
    public function pushToAdded($type, $hook, array $args)
    {
        return $this->pushToStorage(self::ADDED, $type, $hook, $args);
    }

    /**
     * @param hook-type $type
     * @param hook-name $hook
     * @param array $args
     * @return bool
     */
    public function removeFromAdded($type, $hook, array $args)
    {
        if (!$this->isHookAdded($type, $hook)) {
            return false;
        }

        if (!$args) {
            unset($this->storage[self::ADDED][$type][$hook]);

            return true;
        }

        $args = $this->parseArgsToAdd($args, self::ADDED, $type);

        /** @var list<added-hook-args> $all */
        $all = $this->storage[self::ADDED][$type][$hook];
        $removed = 0;

        foreach ($all as $key => list($callback, $priority)) {
            if ($callback->equals($args[0]) && ($priority === $args[1])) {
                unset($all[$key]);
                $removed++;
            }
        }

        $removed and $this->storage[self::ADDED][$type][$hook] = array_values($all);
        if (!$this->storage[self::ADDED][$type][$hook]) {
            unset($this->storage[self::ADDED][$type][$hook]);
        }

        return $removed > 0;
    }

    /**
     * @param hook-type $type
     * @param hook-name $hook
     * @param array $args
     * @return static
     */
    public function pushToDone($type, $hook, array $args)
    {
        return $this->pushToStorage(self::DONE, $type, $hook, $args);
    }

    /**
     * @param hook-type $type
     * @param hook-name $hook
     * @param callable|null $function
     * @return bool
     */
    public function isHookAdded($type, $hook, $function = null)
    {
        assert(is_string($hook));

        return $this->isInStorage(self::ADDED, $type, $hook, $function);
    }

    /**
     * @param hook-type $type
     * @param hook-name $hook
     * @return int
     */
    public function isHookDone($type, $hook)
    {
        assert(is_string($hook));

        return $this->isInStorage(self::DONE, $type, $hook);
    }

    /**
     * @param hook-type $type
     * @param hook-name $hook
     * @param callable $function
     * @return bool|int
     */
    public function hookPriority($type, $hook, $function)
    {
        assert(is_string($type));
        assert(is_string($hook));

        if (!isset($this->storage[self::ADDED][$type][$hook])) {
            return false;
        }

        /** @var list<added-hook-args> $all */
        $all = $this->storage[self::ADDED][$type][$hook];

        foreach ($all as list($callback, $priority)) {
            if ($callback->equals(new CallbackStringForm($function))) {
                return $priority;
            }
        }

        return false;
    }

    /**
     * @param HookStorage::ADDED|HookStorage::DONE $storageType
     * @param hook-type $hookType
     * @param hook-name $hook
     * @param array $args
     * @return static
     */
    private function pushToStorage($storageType, $hookType, $hook, array $args)
    {
        if (!is_string($hook)) {
            throw Exception\InvalidHookArgument::forInvalidHook($hook);
        }

        if (($hookType !== self::ACTIONS) && ($hookType !== self::FILTERS)) {
            throw Exception\InvalidHookArgument::forInvalidType($hookType);
        }

        // do_action() is the only of the target functions that doesn't require additional arguments
        if (!$args && (($storageType !== self::DONE) || ($hookType !== self::ACTIONS))) {
            throw Exception\InvalidHookArgument::forEmptyArguments($storageType, $hookType);
        }

        $storage = &$this->storage[$storageType];

        /** @psalm-suppress InvalidArrayOffset */
        array_key_exists($hookType, $storage) or $storage[$hookType] = [];
        array_key_exists($hook, $storage[$hookType]) or $storage[$hookType][$hook] = [];

        if ($storageType === self::ADDED) {
            $args = $this->parseArgsToAdd($args, $storageType, $hookType);
        }

        $storage[$hookType][$hook][] = $args;

        return $this;
    }

    /**
     * @param HookStorage::ADDED|HookStorage::DONE $storageType
     * @param hook-type $type
     * @param hook-name $hook
     * @param callable|null $function
     * @return ($storageType is HookStorage::ADDED ? bool : int)
     */
    private function isInStorage($storageType, $type, $hook, $function = null)
    {
        $storage = $this->storage[$storageType];

        if (!in_array($type, [self::ACTIONS, self::FILTERS], true)) {
            throw Exception\InvalidHookArgument::forInvalidType($type);
        }

        if (!array_key_exists($type, $storage) || !array_key_exists($hook, $storage[$type])) {
            return ($storageType === self::ADDED) ? false : 0;
        }

        if ($function === null) {
            return ($storageType === self::ADDED) ? true : count($storage[$type][$hook]);
        }

        /**
         * If here, $function is not null, hence we are searching for an added hook, so we can
         * assert $hookArgs is added hooks args.
         *
         * @var list<added-hook-args> $hookArgs
         */
        $hookArgs = $storage[$type][$hook];
        foreach ($hookArgs as list($callable)) {
            if ($callable->equals(new CallbackStringForm($function))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $args
     * @param HookStorage::ADDED|HookStorage::DONE $key
     * @param hook-type $type
     * @return added-hook-args
     */
    private function parseArgsToAdd(array $args, $key, $type)
    {
        if (!$args) {
            throw Exception\InvalidHookArgument::forEmptyArguments($key, $type);
        }

        if (count($args) > 3) {
            throw Exception\InvalidAddedHookArgument::forWrongArgumentsCount($type);
        }

        $args = array_values($args);
        if (empty($args[0])) {
            throw Exception\InvalidAddedHookArgument::forMissingCallback($type);
        }

        $callable = new CallbackStringForm($args[0]);
        $priority = isset($args[1]) ? $args[1] : 10;
        $acceptedArgs = isset($args[2]) ? $args[2] : 1;

        if (!is_int($priority)) {
            throw Exception\InvalidAddedHookArgument::forInvalidPriority($type);
        }

        if (!is_int($acceptedArgs)) {
            throw Exception\InvalidAddedHookArgument::forInvalidAcceptedArgs($type);
        }

        return [$callable, $priority, $acceptedArgs];
    }
}

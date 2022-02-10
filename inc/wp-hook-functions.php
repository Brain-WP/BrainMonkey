<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @package Brain\Monkey
 *
 * As the functions in this file are a compatibility layer for WordPress, the function names and
 * signatures are matching what is currently used in WordPress.
 * That takes precedence over project coding styles.
 *
 * phpcs:disable Inpsyde.CodeQuality.VariablesName.SnakeCaseVar
 */

use Brain\Monkey;

if (!function_exists('add_action')) {
    /**
     * @param string $hook_name
     * @param callable $callback
     * @param int|float $priority
     * @param int $accepted_args
     * @return bool
     */
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        assert(is_string($hook_name));
        assert(is_numeric($priority));
        assert(is_int($accepted_args));

        $args = [$callback, (int)$priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeAddAction($hook_name, $args);

        return true;
    }
}

if (!function_exists('add_filter')) {
    /**
     * @param string $hook_name
     * @param callable $callback
     * @param int|float $priority
     * @param int $accepted_args
     * @return bool
     */
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        assert(is_string($hook_name));
        assert(is_numeric($priority));
        assert(is_int($accepted_args));

        $args = [$callback, (int)$priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeAddFilter($hook_name, $args);

        return true;
    }
}

if (!function_exists('do_action')) {
    /**
     * @param string $hook_name
     * @param mixed ...$arg
     * @return void
     *
     * @psalm-suppress MissingReturnType
     */
    function do_action($hook_name, ...$arg)
    {
        assert(is_string($hook_name));

        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $arg);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $arg);
    }
}

if (!function_exists('do_action_ref_array')) {
    /**
     * @param string $hook_name
     * @param array $args
     * @return void
     *
     * @psalm-suppress MissingReturnType
     */
    function do_action_ref_array($hook_name, $args)
    {
        assert(is_string($hook_name));
        assert(is_array($args));

        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $args);
    }
}

if (!function_exists('do_action_deprecated')) {
    /**
     * @param string $hook_name
     * @param array $args
     * @param string $version
     * @param string $replacement
     * @param string $message
     * @return void
     *
     * @psalm-suppress UnusedParam
     * @psalm-suppress UnusedVariable
     * @psalm-suppress MissingReturnType
     */
    function do_action_deprecated($hook_name, $args, $version, $replacement = '', $message = '')
    {
        assert(is_string($hook_name));

        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $args);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * @param string $hook_name
     * @param mixed $value
     * @return mixed
     */
    function apply_filters($hook_name, $value)
    {
        $args = func_get_args();
        array_shift($args);

        assert(is_string($hook_name));

        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if (!function_exists('apply_filters_ref_array')) {
    /**
     * @param string $hook_name
     * @param array $args
     * @return mixed
     */
    function apply_filters_ref_array($hook_name, $args)
    {
        assert(is_string($hook_name));
        assert(is_array($args));

        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if (!function_exists('apply_filters_deprecated')) {
    /**
     * @param string $hook_name
     * @param array $args
     * @param string $version
     * @param string $replacement
     * @param string $message
     * @return mixed
     *
     * @psalm-suppress UnusedParam
     * @psalm-suppress UnusedVariable
     * @psalm-suppress MissingReturnType
     */
    function apply_filters_deprecated($hook_name, $args, $version, $replacement = '', $message = '')
    {
        assert(is_string($hook_name));
        assert(is_array($args));

        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if (!function_exists('has_action')) {
    /**
     * @param string $hook_name
     * @param callable|false $callback
     * @return bool|int
     */
    function has_action($hook_name, $callback = false)
    {
        assert(is_string($hook_name));

        return Monkey\Actions\has($hook_name, ($callback === false) ? null : $callback);
    }
}

if (!function_exists('has_filter')) {
    /**
     * @param string $hook_name
     * @param callable|false $callback
     * @return bool|int
     */
    function has_filter($hook_name, $callback = false)
    {
        assert(is_string($hook_name));

        return Monkey\Filters\has($hook_name, ($callback === false) ? null : $callback);
    }
}

if (!function_exists('did_action')) {
    /**
     * @param string $hook_name
     * @return int
     */
    function did_action($hook_name)
    {
        assert(is_string($hook_name));

        return Monkey\Actions\did($hook_name);
    }
}

if (!function_exists('remove_action')) {
    /**
     * @param string $hook_name
     * @param callable $callback
     * @param int|float $priority
     * @return bool
     */
    function remove_action($hook_name, $callback, $priority = 10)
    {
        assert(is_string($hook_name));
        assert(is_numeric($priority));

        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$callback, (int)$priority];

        $container->hookExpectationExecutor()->executeRemoveAction($hook_name, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
    }
}

if (!function_exists('remove_filter')) {
    /**
     * @param string $hook_name
     * @param callable $callback
     * @param int|float $priority
     * @return bool
     */
    function remove_filter($hook_name, $callback, $priority = 10)
    {
        assert(is_string($hook_name));
        assert(is_numeric($priority));

        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$callback, (int)$priority];

        $container->hookExpectationExecutor()->executeRemoveFilter($hook_name, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);
    }
}

if (!function_exists('doing_action')) {
    /**
     * @param string|null $hook_name
     * @return bool
     */
    function doing_action($hook_name = null)
    {
        assert(($hook_name === null) || is_string($hook_name));

        return Monkey\Actions\doing($hook_name);
    }
}

if (!function_exists('doing_filter')) {
    /**
     * @param string|null $hook_name
     * @return bool
     */
    function doing_filter($hook_name = null)
    {
        assert(($hook_name === null) || is_string($hook_name));

        return Monkey\Filters\doing($hook_name);
    }
}

if (!function_exists('current_filter')) {
    /**
     * @return false|string
     */
    function current_filter()
    {
        return Monkey\Container::instance()->hookRunningStack()->last() ?: false;
    }
}

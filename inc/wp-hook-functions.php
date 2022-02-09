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
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        $args = [$callback, $priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeAddAction($hook_name, $args);

        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        $args = [$callback, $priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeAddFilter($hook_name, $args);

        return true;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook_name, ...$arg)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $arg);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $arg);
    }
}

if (!function_exists('do_action_ref_array')) {
    function do_action_ref_array($hook_name, $args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $args);
    }
}

if (!function_exists('do_action_deprecated')) {
    function do_action_deprecated($hook_name, $args, $version, $replacement = '', $message = '')
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $args);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook_name, $value)
    {
        $args = func_get_args();
        array_shift($args);
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if (!function_exists('apply_filters_ref_array')) {
    function apply_filters_ref_array($hook_name, $args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if (!function_exists('apply_filters_deprecated')) {
    function apply_filters_deprecated($hook_name, $args, $version, $replacement = '', $message = '')
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if (!function_exists('has_action')) {
    function has_action($hook_name, $callback = false)
    {
        return Monkey\Actions\has($hook_name, ($callback === false) ? null : $callback);
    }
}

if (!function_exists('has_filter')) {
    function has_filter($hook_name, $callback = false)
    {
        return Monkey\Filters\has($hook_name, ($callback === false) ? null : $callback);
    }
}

if (!function_exists('did_action')) {
    function did_action($hook_name)
    {
        return Monkey\Actions\did($hook_name);
    }
}

if (!function_exists('remove_action')) {
    function remove_action($hook_name, $callback, $priority = 10)
    {
        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$callback, $priority];

        $container->hookExpectationExecutor()->executeRemoveAction($hook_name, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
    }
}

if (!function_exists('remove_filter')) {
    function remove_filter($hook_name, $callback, $priority = 10)
    {
        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$callback, $priority];

        $container->hookExpectationExecutor()->executeRemoveFilter($hook_name, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);
    }
}

if (!function_exists('doing_action')) {
    function doing_action($hook_name = null)
    {
        return Monkey\Actions\doing($hook_name);
    }
}

if (!function_exists('doing_filter')) {
    function doing_filter($hook_name = null)
    {
        return Monkey\Filters\doing($hook_name);
    }
}

if (!function_exists('current_filter')) {
    function current_filter()
    {
        return Monkey\Container::instance()->hookRunningStack()->last() ?: false;
    }
}

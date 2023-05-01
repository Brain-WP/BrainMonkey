<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */

use Brain\Monkey;

if ( ! function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        $args = [$callback, $priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeAddAction($hook_name, $args);

        return true;
    }
}

if ( ! function_exists('add_filter')) {
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        $args = [$callback, $priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeAddFilter($hook_name, $args);

        return true;
    }
}

if ( ! function_exists('do_action')) {
    function do_action($hook_name, ...$args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $args);
    }
}

if ( ! function_exists('do_action_ref_array')) {
    function do_action_ref_array($hook_name, array $args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $args);
    }
}

if ( ! function_exists('do_action_deprecated')) {
    function do_action_deprecated($hook_name, array $args, $version, $replacement, $message = null)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
        $container->hookExpectationExecutor()->executeDoAction($hook_name, $args);
    }
}

if ( ! function_exists('apply_filters')) {
    function apply_filters($hook_name, ...$args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if ( ! function_exists('apply_filters_ref_array')) {
    function apply_filters_ref_array($hook_name, array $args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if ( ! function_exists('apply_filters_deprecated')) {
    function apply_filters_deprecated($hook_name, array $args, $version, $replacement, $message = null)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($hook_name, $args);
    }
}

if ( ! function_exists('has_action')) {
    function has_action($hook_name, $callback = null)
    {
        return Monkey\Actions\has($hook_name, $callback);
    }
}

if ( ! function_exists('has_filter')) {
    function has_filter($hook_name, $callback = null)
    {
        return Monkey\Filters\has($hook_name, $callback);
    }
}

if ( ! function_exists('did_action')) {
    function did_action($hook_name)
    {
        return Monkey\Actions\did($hook_name);
    }
}

if ( ! function_exists('remove_action')) {
    function remove_action($hook_name, $callback, $priority = 10)
    {
        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$callback, $priority];

        $container->hookExpectationExecutor()->executeRemoveAction($hook_name, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::ACTIONS, $hook_name, $args);
    }
}

if ( ! function_exists('remove_filter')) {
    function remove_filter($hook_name, $callback, $priority = 10)
    {
        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$callback, $priority];

        $container->hookExpectationExecutor()->executeRemoveFilter($hook_name, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::FILTERS, $hook_name, $args);
    }
}

if ( ! function_exists('doing_action')) {
    function doing_action($hook_name)
    {
        return Monkey\Actions\doing($hook_name);
    }
}

if ( ! function_exists('doing_filter')) {
    function doing_filter($hook_name)
    {
        return Monkey\Filters\doing($hook_name);
    }
}

if ( ! function_exists('current_filter')) {
    function current_filter()
    {
        return Monkey\Container::instance()->hookRunningStack()->last() ? : false;
    }
}

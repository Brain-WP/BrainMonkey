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
    function add_action($action, $function, $priority = 10, $accepted_args = 1)
    {
        $args = [$function, $priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::ACTIONS, $action, $args);
        $container->hookExpectationExecutor()->executeAddAction($action, $args);

        return true;
    }
}

if ( ! function_exists('add_filter')) {
    function add_filter($filter, $function, $priority = 10, $accepted_args = 1)
    {
        $args = [$function, $priority, $accepted_args];
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToAdded(Monkey\Hook\HookStorage::FILTERS, $filter, $args);
        $container->hookExpectationExecutor()->executeAddFilter($filter, $args);

        return true;
    }
}

if ( ! function_exists('do_action')) {
    function do_action($action, ...$args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $action, $args);
        $container->hookExpectationExecutor()->executeDoAction($action, $args);
    }
}

if ( ! function_exists('do_action_ref_array')) {
    function do_action_ref_array($action, array $args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $action, $args);
        $container->hookExpectationExecutor()->executeDoAction($action, $args);
    }
}

if ( ! function_exists('do_action_deprecated')) {
    function do_action_deprecated($action, array $args, $version, $replacement, $message = null)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::ACTIONS, $action, $args);
        $container->hookExpectationExecutor()->executeDoAction($action, $args);
    }
}

if ( ! function_exists('apply_filters')) {
    function apply_filters($filter, ...$args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $filter, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($filter, $args);
    }
}

if ( ! function_exists('apply_filters_ref_array')) {
    function apply_filters_ref_array($filter, array $args)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $filter, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($filter, $args);
    }
}

if ( ! function_exists('apply_filters_deprecated')) {
    function apply_filters_deprecated($filter, array $args, $version, $replacement, $message = null)
    {
        $container = Monkey\Container::instance();
        $container->hookStorage()->pushToDone(Monkey\Hook\HookStorage::FILTERS, $filter, $args);

        return $container->hookExpectationExecutor()->executeApplyFilters($filter, $args);
    }
}

if ( ! function_exists('has_action')) {
    function has_action($action, $callback = null)
    {
        return Monkey\Actions\has($action, $callback);
    }
}

if ( ! function_exists('has_filter')) {
    function has_filter($filter, $callback = null)
    {
        return Monkey\Filters\has($filter, $callback);
    }
}

if ( ! function_exists('did_action')) {
    function did_action($action)
    {
        return Monkey\Actions\did($action);
    }
}

if ( ! function_exists('remove_action')) {
    function remove_action($action, $function, $priority = 10)
    {
        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$function, $priority];

        $container->hookExpectationExecutor()->executeRemoveAction($action, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::ACTIONS, $action, $args);
    }
}

if ( ! function_exists('remove_filter')) {
    function remove_filter($filter, $function, $priority = 10)
    {
        $container = Monkey\Container::instance();
        $storage = $container->hookStorage();
        $args = [$function, $priority];

        $container->hookExpectationExecutor()->executeRemoveFilter($filter, $args);

        return $storage->removeFromAdded(Monkey\Hook\HookStorage::FILTERS, $filter, $args);
    }
}

if ( ! function_exists('doing_action')) {
    function doing_action($action)
    {
        return Monkey\Actions\doing($action);
    }
}

if ( ! function_exists('doing_filter')) {
    function doing_filter($filter)
    {
        return Monkey\Filters\doing($filter);
    }
}

if ( ! function_exists('current_filter')) {
    function current_filter()
    {
        return Monkey\Container::instance()->hookRunningStack()->last() ? : false;
    }
}

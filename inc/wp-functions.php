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

use Brain\Monkey\WP\Hooks;

if (! function_exists('add_action')) {
    function add_action()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'add'.Hooks::ACTION],
            func_get_args()
        );
    }
}

if (! function_exists('remove_action')) {
    function remove_action()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'remove'.Hooks::ACTION],
            func_get_args()
        );
    }
}

if (! function_exists('do_action')) {
    function do_action()
    {
        call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'run'.Hooks::ACTION],
            func_get_args()
        );
    }
}

if (! function_exists('do_action_ref_array')) {
    function do_action_ref_array()
    {
        call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'runRef'.Hooks::ACTION],
            func_get_args()
        );
    }
}

if (! function_exists('did_action')) {
    function did_action()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'did'.Hooks::ACTION],
            func_get_args()
        );
    }
}

if (! function_exists('has_action')) {
    function has_action()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'has'.Hooks::ACTION],
            func_get_args()
        );
    }
}

if (! function_exists('add_filter')) {
    function add_filter()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'add'.Hooks::FILTER],
            func_get_args()
        );
    }
}

if (! function_exists('remove_filter')) {
    function remove_filter()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'remove'.Hooks::FILTER],
            func_get_args()
        );
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'run'.Hooks::FILTER],
            func_get_args()
        );
    }
}

if (! function_exists('apply_filters_ref_array')) {
    function apply_filters_ref_array()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'runRef'.Hooks::FILTER],
            func_get_args()
        );
    }
}

if (! function_exists('has_filter')) {
    function has_filter()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'has'.Hooks::FILTER],
            func_get_args()
        );
    }
}

if (! function_exists('current_filter')) {
    function current_filter()
    {
        return call_user_func_array(
            ['Brain\Monkey\WP\Hooks', 'current'],
            func_get_args()
        );
    }
}

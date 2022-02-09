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
 * phpcs:disable PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames
 */

if (!function_exists('__return_true')) {
    function __return_true()
    {
        return true;
    }
}

if (!function_exists('__return_false')) {
    function __return_false()
    {
        return false;
    }
}

if (!function_exists('__return_null')) {
    function __return_null()
    {
        return null;
    }
}

if (!function_exists('__return_zero')) {
    function __return_zero()
    {
        return 0;
    }
}

if (!function_exists('__return_empty_array')) {
    function __return_empty_array()
    {
        return [];
    }
}

if (!function_exists('__return_empty_string')) {
    function __return_empty_string()
    {
        return '';
    }
}

if (!function_exists('untrailingslashit')) {
    function untrailingslashit($string)
    {
        return rtrim($string, '/\\');
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit($string)
    {
        return rtrim($string, '/\\') . '/';
    }
}

if (!function_exists('user_trailingslashit')) {
    function user_trailingslashit($string)
    {
        return trailingslashit($string);
    }
}

if (!function_exists('absint')) {
    function absint($number)
    {
        return abs((int)$number);
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing)
    {
        return $thing instanceof \WP_Error;
    }
}

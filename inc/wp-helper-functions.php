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
    /**
     * @return true
     */
    function __return_true()
    {
        return true;
    }
}

if (!function_exists('__return_false')) {
    /**
     * @return false
     */
    function __return_false()
    {
        return false;
    }
}

if (!function_exists('__return_null')) {
    /**
     * @return null
     */
    function __return_null()
    {
        return null;
    }
}

if (!function_exists('__return_zero')) {
    /**
     * @return int
     */
    function __return_zero()
    {
        return 0;
    }
}

if (!function_exists('__return_empty_array')) {
    /**
     * @return array
     */
    function __return_empty_array()
    {
        return [];
    }
}

if (!function_exists('__return_empty_string')) {
    /**
     * @return string
     */
    function __return_empty_string()
    {
        return '';
    }
}

if (!function_exists('untrailingslashit')) {
    /**
     * @param string $string
     * @return string
     */
    function untrailingslashit($string)
    {
        assert(is_string($string));

        return rtrim($string, '/\\');
    }
}

if (!function_exists('trailingslashit')) {
    /**
     * @param string $string
     * @return string
     */
    function trailingslashit($string)
    {
        assert(is_string($string));

        return rtrim($string, '/\\') . '/';
    }
}

if (!function_exists('user_trailingslashit')) {
    /**
     * @param string $string
     * @return string
     */
    function user_trailingslashit($string)
    {
        assert(is_string($string));

        return trailingslashit($string);
    }
}

if (!function_exists('absint')) {
    /**
     * @param int|float $number
     * @return int
     */
    function absint($number)
    {
        assert(is_numeric($number));

        return abs((int)$number);
    }
}

if (!function_exists('wp_json_encode')) {
    /**
     * @param mixed $data
     * @param int $options
     * @param int $depth
     * @return false|string
     */
    function wp_json_encode($data, $options = 0, $depth = 512)
    {
        assert(is_int($options));
        assert(is_int($depth));

        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('is_wp_error')) {
    /**
     * @param mixed $thing
     * @return bool
     */
    function is_wp_error($thing)
    {
        return class_exists(\WP_Error::class) && $thing instanceof \WP_Error;
    }
}

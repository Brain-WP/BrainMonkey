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

if ( ! function_exists('__return_true')) {
    function __return_true()
    {
        return true;
    }
}

if ( ! function_exists('__return_false')) {
    function __return_false()
    {
        return false;
    }
}

if ( ! function_exists('__return_null')) {
    function __return_null()
    {
        return null;
    }
}

if ( ! function_exists('__return_zero')) {
    function __return_zero()
    {
        return 0;
    }
}

if ( ! function_exists('__return_empty_array')) {
    function __return_empty_array()
    {
        return [];
    }
}

if ( ! function_exists('__return_empty_string')) {
    function __return_empty_string()
    {
        return '';
    }
}

if ( ! function_exists('untrailingslashit')) {
    function untrailingslashit($string)
    {
        return rtrim($string, '/\\');
    }
}

if ( ! function_exists('trailingslashit')) {
    function trailingslashit($string)
    {
        return rtrim($string, '/\\').'/';
    }
}
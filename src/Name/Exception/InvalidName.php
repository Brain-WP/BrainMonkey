<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Name\Exception;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class InvalidName extends Exception
{

    const CODE_FOR_FUNCTION = 1;
    const CODE_FOR_CLASS    = 2;
    const CODE_FOR_METHOD   = 3;

    /**
     * @param string $function
     * @return \Brain\Monkey\Name\Exception\InvalidName
     */
    public static function forFunction($function)
    {
        return self::createFor($function, self::CODE_FOR_FUNCTION);
    }

    /**
     * @param string $class
     * @return \Brain\Monkey\Name\Exception\InvalidName
     */
    public static function forClass($class)
    {
        return self::createFor($class, self::CODE_FOR_CLASS);
    }

    /**
     * @param string $function
     * @return \Brain\Monkey\Name\Exception\InvalidName
     */
    public static function forMethod($function)
    {
        return self::createFor($function, self::CODE_FOR_METHOD);
    }

    /**
     * @param mixed $thing
     * @param int   $code
     * @return static
     */
    private static function createFor($thing, $code)
    {
        switch ($code) {
            case self::CODE_FOR_CLASS:
                $type = 'class';
                break;
            case self::CODE_FOR_METHOD:
                $type = 'class method';
                break;
            case self::CODE_FOR_FUNCTION:
            default:
                $type = 'function';
                break;
        }

        switch (true) {
            case is_string($thing):
                $name = "'{$thing}'";
                break;
            case is_object($thing):
                $name = 'An instance of '.get_class($thing);
                break;
            default:
                $name = 'A variable of type '.gettype($thing);
        }

        return new static(sprintf('%s is not a valid %s name.', $name, $type), $code);
    }

}

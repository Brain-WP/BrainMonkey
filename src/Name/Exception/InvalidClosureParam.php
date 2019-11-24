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
class InvalidClosureParam extends Exception
{

    const CODE_INVALID_NAME      = 1;
    const CODE_INVALID_TYPE      = 2;
    const CODE_MULTIPLE_VARIADIC = 3;

    /**
     * @param string $name
     * @return static
     */
    public static function forInvalidName($name)
    {
        return new static(
            sprintf('%s is not a valid function argument name.', $name),
            self::CODE_INVALID_NAME
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @return static
     */
    public static function forInvalidType($type, $name)
    {
        return new static(
            sprintf('%s is not a valid function argument type for argument %s.', $type, $name),
            self::CODE_INVALID_TYPE
        );
    }

    /**
     * @param string $name
     * @return static
     */
    public static function forMultipleVariadic($name)
    {
        return new static(
            sprintf(
                '%s is a variadic argument for a function that already has a variadic argument.',
                $name
            ),
            self::CODE_MULTIPLE_VARIADIC
        );
    }

}
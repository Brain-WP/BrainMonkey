<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation\Exception;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class NotAllowedMethod extends Exception
{

    const CODE_METHOD           = 1;
    const CODE_RETURNING_METHOD = 2;

    /**
     * @param string $method_name
     * @return static
     */
    public static function forMethod($method_name)
    {
        return new static(
            sprintf(
                '%s method is not allowed for Brain Monkey expectation.',
                $method_name
            ),
            self::CODE_METHOD
        );
    }

    /**
     * @param string $method_name
     * @return static
     */
    public static function forReturningMethod($method_name)
    {
        return new static(
            sprintf(
                'Bad usage of "%s" method: returning expectation can only be used for functions or applied filters expectations.',
                $method_name
            ),
            self::CODE_RETURNING_METHOD
        );
    }

}
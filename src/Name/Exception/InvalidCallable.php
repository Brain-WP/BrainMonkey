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
class InvalidCallable extends Exception
{

    /**
     * @param mixed $callback
     * @return \Brain\Monkey\Name\Exception\InvalidCallable|\Brain\Monkey\Name\Exception\NotInvokableObjectAsCallback
     */
    public static function forCallable($callback)
    {
        if (is_object($callback)) {
            return new NotInvokableObjectAsCallback();
        }

        return new static(
            sprintf(
                'Given %s "%s" is not a valid PHP callable.',
                gettype($callback),
                is_string($callback) ? "{$callback}" : var_export($callback, true)
            )
        );

    }

}
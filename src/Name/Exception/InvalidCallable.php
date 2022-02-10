<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Name\Exception;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class InvalidCallable extends Exception
{
    /**
     * @param mixed $callback
     * @return InvalidCallable|NotInvokableObjectAsCallback
     */
    public static function forCallable($callback)
    {
        if (is_object($callback)) {
            return new NotInvokableObjectAsCallback();
        }

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                'Given %s "%s" is not a valid PHP callable.',
                gettype($callback),
                is_string($callback) ? "{$callback}" : var_export($callback, true)
            )
        );
    }
}

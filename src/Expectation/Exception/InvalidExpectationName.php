<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation\Exception;

use Brain\Monkey\Expectation\ExpectationTarget;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class InvalidExpectationName extends Exception
{
    /**
     * @param mixed $name
     * @param string $type
     * @return static
     */
    public static function forNameAndType($name, $type)
    {
        return new static(
            sprintf(
                '%s name to set expectation for must be in a string, got %s.',
                ($type === ExpectationTarget::TYPE_FUNCTION) ? 'Function' : 'Hook',
                is_object($name) ? 'instance of ' . get_class($name) : gettype($name)
            )
        );
    }
}

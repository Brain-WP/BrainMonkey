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

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class InvalidExpectationType extends Exception
{
    /**
     * @param string $type
     * @return static
     */
    public static function forType($type)
    {
        return new static(
            sprintf(
                '%s method is not allowed for Brain Monkey expectation.',
                $type
            )
        );
    }
}

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
class NotAllowedMethod extends Exception
{
    const CODE_METHOD = 1;
    const CODE_RETURNING_METHOD = 2;
    const CODE_WHEN_HAPPEN = 3;
    const CODE_BY_DEFAULT = 4;

    /**
     * @param string $methodName
     * @return static
     */
    public static function forMethod($methodName)
    {
        return new static(
            sprintf(
                '%s method is not allowed for Brain Monkey expectation.',
                $methodName
            ),
            self::CODE_METHOD
        );
    }

    /**
     * @return static
     */
    public static function forByDefault()
    {
        return new static(
            'byDefault method is not allowed for Brain Monkey hook expectation.',
            self::CODE_BY_DEFAULT
        );
    }

    /**
     * @param string $methodName
     * @return static
     */
    public static function forReturningMethod($methodName)
    {
        return new static(
            sprintf(
                'Bad usage of "%s" method: returning expectation can only be used for functions '
                . 'or applied filters expectations.',
                $methodName
            ),
            self::CODE_RETURNING_METHOD
        );
    }

    public static function forWhenHappen(ExpectationTarget $target)
    {
        $type = '';

        switch ($target->type()) {
            case ExpectationTarget::TYPE_FUNCTION:
                $type = "function";
                break;
            case ExpectationTarget::TYPE_FILTER_APPLIED:
                $type = "applied filter";
                break;
        }

        return new static(
            "Can't use `whenHappen()` for {$type} expectations: use `andReturnUsing()` instead.",
            self::CODE_WHEN_HAPPEN
        );
    }
}

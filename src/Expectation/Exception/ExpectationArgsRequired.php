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
class ExpectationArgsRequired extends Exception
{
    /**
     * @param \Brain\Monkey\Expectation\ExpectationTarget $target
     * @return static
     */
    public static function forExpectationType(ExpectationTarget $target)
    {
        $type = 'given';

        switch ($target->type()) {
            case ExpectationTarget::TYPE_ACTION_ADDED:
                $type = "added action";
                break;
            case ExpectationTarget::TYPE_ACTION_DONE:
                $type = "done action";
                break;
            case ExpectationTarget::TYPE_FILTER_ADDED:
                $type = "added filter";
                break;
            case ExpectationTarget::TYPE_FILTER_APPLIED:
                $type = "applied filter";
                break;
        }

        return new static(
            "Can't use `withNoArgs()` for {$type} expectations: they require at least one argument."
        );
    }
}

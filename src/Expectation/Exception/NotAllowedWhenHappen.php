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

use Brain\Monkey\Expectation\ExpectationTarget;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class NotAllowedWhenHappen extends Exception
{

    /**
     * @param \Brain\Monkey\Expectation\ExpectationTarget $target
     * @return static
     */
    public static function forExpectationType(ExpectationTarget $target)
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
            "Can't use `whenHappen()` for {$type} expectations: use `andReturnUsing()` instead."
        );
    }

}
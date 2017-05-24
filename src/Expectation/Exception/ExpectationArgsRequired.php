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
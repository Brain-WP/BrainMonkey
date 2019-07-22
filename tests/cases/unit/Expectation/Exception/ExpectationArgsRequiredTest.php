<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation\Exception;


use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\UnitTestCase;

class ExpectationArgsRequiredTest extends UnitTestCase
{

    /**
     * @dataProvider expectationTargets
     * @param string $type
     * @param string $message_part
     */
    public function testBecauseOf($type, $message_part)
    {
        $message = ExpectationArgsRequired::forExpectationType(
            new ExpectationTarget($type, 'foo')
        )->getMessage();

        static::assertContains($message_part, $message);

    }

    public function expectationTargets()
    {
        return [
            [ExpectationTarget::TYPE_ACTION_ADDED, 'added action'],
            [ExpectationTarget::TYPE_ACTION_DONE, 'done action'],
            [ExpectationTarget::TYPE_FILTER_ADDED, 'added filter'],
            [ExpectationTarget::TYPE_FILTER_APPLIED, 'applied filter'],
        ];
    }
}

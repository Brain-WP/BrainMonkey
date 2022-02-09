<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation\Exception;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExpectationArgsRequiredTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider provideExpectationTargets
     */
    public function testBecauseOf($type, $messagePart)
    {
        $message = ExpectationArgsRequired::forExpectationType(
            new ExpectationTarget($type, 'foo')
        )->getMessage();

        static::assertStringContains($messagePart, $message);
    }

    /**
     * @return list<array{string, string}>
     */
    public function provideExpectationTargets()
    {
        return [
            [ExpectationTarget::TYPE_ACTION_ADDED, 'added action'],
            [ExpectationTarget::TYPE_ACTION_DONE, 'done action'],
            [ExpectationTarget::TYPE_FILTER_ADDED, 'added filter'],
            [ExpectationTarget::TYPE_FILTER_APPLIED, 'applied filter'],
        ];
    }
}

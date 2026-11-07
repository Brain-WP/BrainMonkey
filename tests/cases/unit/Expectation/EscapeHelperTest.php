<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation;

use Brain\Monkey\Expectation\EscapeHelper;
use Brain\Monkey\Tests\UnitTestCase;

class EscapeHelperTest extends UnitTestCase
{
    /**
     * @dataProvider escUrlDataProvider
     */
    public function testEscUrlAndRaw($input, $expected, $raw)
    {
        $actual = $raw
            ? EscapeHelper::escUrlRaw($input)
            : EscapeHelper::escUrl($input);

        static::assertSame($expected, $actual);
    }

    public static function escUrlDataProvider()
    {
        return [
            ['http://example.com', 'http://example.com', true],
            ['http://example.com', 'http://example.com', false],
            ['https://example.com', 'https://example.com', true],
            ['https://example.com', 'https://example.com', false],
            ['x', 'http://x', true],
            ['x', 'http://x', false],
            ['', '', true],
            ['', '', false],
            [' ', '', true],
            [' ', '', false],
            ['http://example.com?x=y&amp;', "http://example.com?x=y&amp;", true],
            ['http://example.com?x=y&amp;', "http://example.com?x=y&#038;", false],
            ["http://example.com?x=y'", "http://example.com?x=y'", true],
            ["http://example.com?x=y'", "http://example.com?x=y&#039;", false],
            ["http://example.com?x=y&#038;&#039;", "http://example.com?x=y&#038;&#039;", true],
            ["http://example.com?x=y&#038;&#039;", "http://example.com?x=y&#038;&#039;", false],
        ];
    }
}

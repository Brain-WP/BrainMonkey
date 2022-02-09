<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Name;

use Brain\Monkey\Name\ClosureStringForm;
use Brain\Monkey\Name\Exception\InvalidCallable;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClosureStringFormTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testNoArg()
    {
        $callback = function () {
            return $this;
        };

        $stringForm = new ClosureStringForm($callback);

        static::assertSame('function ()', (string)$stringForm);
    }

    /**
     * @test
     */
    public function testStaticNoArg()
    {
        $callback = static function () {
        };

        $stringForm = new ClosureStringForm($callback);

        static::assertSame('static function ()', (string)$stringForm);
    }

    /**
     * @test
     */
    public function testArgsNoTypeHint()
    {
        $callback = function ($foo, $bar) {
            return $this;
        };

        $stringForm = new ClosureStringForm($callback);

        static::assertSame('function ($foo, $bar)', (string)$stringForm);
    }

    /**
     * @test
     */
    public function testArgsTypeHints()
    {
        $callback = function (\ArrayObject $foo, array $bar, \stdClass ...$classes) {
            return $this;
        };

        $stringForm = new ClosureStringForm($callback);

        static::assertSame(
            'function (ArrayObject $foo, array $bar, stdClass ...$classes)',
            (string)$stringForm
        );
    }

    /**
     * @test
     */
    public function testStaticArgsTypeHints()
    {
        $callback = static function (\ArrayObject $foo, array $bar, \stdClass ...$classes) {
        };

        $stringForm = new ClosureStringForm($callback);

        static::assertSame(
            'static function (ArrayObject $foo, array $bar, stdClass ...$classes)',
            (string)$stringForm
        );
    }

    /**
     * @test
     */
    public function testInvalidClosureStringThrows()
    {
        $closure = 'function()) {}';
        $this->expectException(InvalidCallable::class);

        ClosureStringForm::normalizeString($closure);
    }

    /**
     * @test
     */
    public function testParseNoArgsString()
    {
        $closure1 = 'function()';
        $closure2 = 'function ()';
        $closure3 = 'static function ()';
        $closure4 = ' static function  ( ) ';

        static::assertSame('function ()', ClosureStringForm::normalizeString($closure1));
        static::assertSame('function ()', ClosureStringForm::normalizeString($closure2));
        static::assertSame('static function ()', ClosureStringForm::normalizeString($closure3));
        static::assertSame('static function ()', ClosureStringForm::normalizeString($closure4));
    }

    /**
     * @test
     */
    public function testParseStringWithArgs()
    {
        $closure = ' static function( \ArrayObject $foo,array $bar,stdClass ...$classes ) ';

        static::assertSame(
            'static function (ArrayObject $foo, array $bar, stdClass ...$classes)',
            ClosureStringForm::normalizeString($closure)
        );
    }
}

<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Name;

use Brain\Monkey\Name\ClosureStringForm;
use Brain\Monkey\Name\Exception\InvalidCallable;
use Brain\Monkey\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClosureStringFormTest extends TestCase
{

    public function testNoArg()
    {
        $callback = function () {

        };

        $string_form = new ClosureStringForm($callback);

        static::assertSame('function ()', (string)$string_form);
    }

    public function testStaticNoArg()
    {
        $callback = static function () {

        };

        $string_form = new ClosureStringForm($callback);

        static::assertSame('static function ()', (string)$string_form);
    }

    public function testArgsNoTypeHint()
    {
        $callback = function ($foo, $bar) {

        };

        $string_form = new ClosureStringForm($callback);

        static::assertSame('function ($foo, $bar)', (string)$string_form);
    }

    public function testArgsTypeHints7()
    {
        if (PHP_MAJOR_VERSION < 7) {
            $this->markTestSkipped('Skipping PHP 7 test.');

            return;
        }

        $callback = function (\ArrayObject $foo, array $bar, \stdClass... $classes) {

        };

        $string_form = new ClosureStringForm($callback);

        static::assertSame(
            'function (ArrayObject $foo, array $bar, stdClass ...$classes)',
            (string)$string_form
        );
    }

    public function testArgsTypeHints5()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->markTestSkipped('Skipping PHP 5.6 test.');

            return;
        }

        $callback = function (\ArrayObject $foo, array $bar, \stdClass... $classes) {

        };

        $string_form = new ClosureStringForm($callback);

        static::assertSame(
            'function ($foo, $bar, ...$classes)',
            (string)$string_form
        );
    }

    public function testStaticArgsTypeHints()
    {
        $callback = static function (\ArrayObject $foo, array $bar, \stdClass... $classes) {

        };

        $string_form = new ClosureStringForm($callback);

        static::assertSame(
            'static function (ArrayObject $foo, array $bar, stdClass ...$classes)',
            (string)$string_form
        );
    }

    public function testInvalidClosureStringThrows()
    {
        $closure = 'function()) {}';
        $this->expectException(InvalidCallable::class);

        ClosureStringForm::normalizeString($closure);
    }

    public function testParseNoArgsString()
    {
        $closure_a = 'function()';
        $closure_b = 'function ()';
        $closure_c = 'static function ()';
        $closure_d = ' static function  ( ) ';

        static::assertSame('function ()', ClosureStringForm::normalizeString($closure_a));
        static::assertSame('function ()', ClosureStringForm::normalizeString($closure_b));
        static::assertSame('static function ()', ClosureStringForm::normalizeString($closure_c));
        static::assertSame('static function ()', ClosureStringForm::normalizeString($closure_d));
    }

    public function testParseStringWithArgs()
    {
        $closure = ' static function( \ArrayObject $foo,array $bar,stdClass ...$classes ) ';

        static::assertSame(
            'static function (ArrayObject $foo, array $bar, stdClass ...$classes)',
            ClosureStringForm::normalizeString($closure)
        );
    }
}
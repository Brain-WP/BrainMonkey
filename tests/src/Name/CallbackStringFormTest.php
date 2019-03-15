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

use Brain\Monkey\Name\CallbackStringForm;
use Brain\Monkey\Name\Exception\InvalidCallable;
use Brain\Monkey\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class CallbackStringFormTest extends TestCase
{

    public function testClosureToString()
    {
        $callback = function () {

        };

        $string_form = new CallbackStringForm($callback);

        static::assertSame('function ()', (string)$string_form);
    }

    public function testFunctionNameToString()
    {
        $string_form = new CallbackStringForm('Foo\Bar\Baz');
        $string_form_escape = new CallbackStringForm('Foo\\Bar\\Baz');

        static::assertSame('Foo\Bar\Baz', (string)$string_form);
        static::assertSame('Foo\Bar\Baz', (string)$string_form_escape);
        static::assertSame('Foo\\Bar\\Baz', (string)$string_form);
        static::assertSame('Foo\\Bar\\Baz', (string)$string_form_escape);
    }

    public function testStaticMethodFromStringToString()
    {
        $string_form = new CallbackStringForm('Foo\Bar\Baz::method');

        static::assertSame('Foo\Bar\Baz::method()', (string)$string_form);
    }

    public function testStaticMethodToString()
    {
        $string_form_a = new CallbackStringForm(['Foo\Bar\Baz', 'method']);
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $string_form_b = new CallbackStringForm([\Foo\Bar\Baz::class, 'method']);
        $string_form_c = new CallbackStringForm([__CLASS__, __FUNCTION__]);

        static::assertSame('Foo\Bar\Baz::method()', (string)$string_form_a);
        static::assertSame('Foo\Bar\Baz::method()', (string)$string_form_b);
        static::assertSame(__CLASS__.'::'.__FUNCTION__.'()', (string)$string_form_c);
    }

    public function testDynamicMethodToString()
    {
        $string_form_a = new CallbackStringForm([new \ArrayObject(), 'getArrayCopy']);
        $string_form_b = new CallbackStringForm([$this, __FUNCTION__]);

        static::assertSame('ArrayObject->getArrayCopy()', (string)$string_form_a);
        static::assertSame(__CLASS__.'->'.__FUNCTION__.'()', (string)$string_form_b);
    }

    /**
     * @dataProvider equalsParams
     * @param $left_param
     * @param $right_param
     * @param $expected
     */
    public function testEquals($left_param, $right_param, $expected)
    {
        $left = new CallbackStringForm($left_param);
        $right = new CallbackStringForm($right_param);

        $expected
            ? static::assertTrue($left->equals($right), "{$left} === {$right}")
            : static::assertFalse($left->equals($right), "{$left} !== {$right}");
    }

    public function equalsParams()
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        return [
            [function ($foo) {}, function ($foo) {}, true],
            [function ($foo) {}, 'function ($foo)', true],
            [function ($foo) {}, function ($bar) {}, false],
            [function ($foo) {}, 'function ($bar)', false],
            ['Foo\Bar\baz', 'Foo\\Bar\\baz', true],
            ['Foo\Bar\baz', 'Foo\\Baz\\baz', false],
            [['Foo\Bar\Baz', 'method'], [\Foo\Bar\Baz::class, 'method'], true],
            [['Foo\Bar\Baz', 'method'], [Foo\Bar\Baz::class, 'method'], false],
            [[new \ArrayObject(), 'getArrayCopy'], [new \ArrayObject(), 'getArrayCopy'], true],
            [[new \ArrayObject(), 'getArrayCopy'], 'ArrayObject->getArrayCopy()', true],
            [[new \ArrayObject(), 'getArrayCopy'], 'ArrayObject::getArrayCopy()', false],
        ];
    }

    public function testFromStringThrowForMalformedClosure()
    {
        $closure = 'function )';
        $this->expectException(InvalidCallable::class);
        new CallbackStringForm($closure);
    }

    public function testFromStringThrowForMalformedClosureArgs()
    {
        $closure = 'function(...)';
        $this->expectException(InvalidCallable::class);
        new CallbackStringForm($closure);
    }
}
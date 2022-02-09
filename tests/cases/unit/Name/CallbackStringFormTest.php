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

use Brain\Monkey\Name\CallbackStringForm;
use Brain\Monkey\Name\Exception\InvalidCallable;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class CallbackStringFormTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testClosureToString()
    {
        $callback = function () {
            return $this;
        };

        $stringForm = new CallbackStringForm($callback);

        static::assertSame('function ()', (string)$stringForm);
    }

    /**
     * @test
     */
    public function testFunctionNameToString()
    {
        $stringForm = new CallbackStringForm('Foo\Bar\Baz');
        $stringFormEscaped = new CallbackStringForm('Foo\\Bar\\Baz');

        static::assertSame('Foo\Bar\Baz', (string)$stringForm);
        static::assertSame('Foo\Bar\Baz', (string)$stringFormEscaped);
        static::assertSame('Foo\\Bar\\Baz', (string)$stringForm);
        static::assertSame('Foo\\Bar\\Baz', (string)$stringFormEscaped);
    }

    /**
     * @test
     */
    public function testStaticMethodToString()
    {
        $stringForm1 = new CallbackStringForm(['Foo\Bar\Baz', 'method']);
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        $stringForm2 = new CallbackStringForm([\Foo\Bar\Baz::class, 'method']);
        $stringForm3 = new CallbackStringForm([__CLASS__, __FUNCTION__]);

        static::assertSame('Foo\Bar\Baz::method()', (string)$stringForm1);
        static::assertSame('Foo\Bar\Baz::method()', (string)$stringForm2);
        static::assertSame(__CLASS__ . '::' . __FUNCTION__ . '()', (string)$stringForm3);
    }

    /**
     * @test
     */
    public function testDynamicMethodToString()
    {
        $stringForm1 = new CallbackStringForm([new \ArrayObject(), 'getArrayCopy']);
        $stringForm2 = new CallbackStringForm([$this, __FUNCTION__]);

        static::assertSame('ArrayObject->getArrayCopy()', (string)$stringForm1);
        static::assertSame(__CLASS__ . '->' . __FUNCTION__ . '()', (string)$stringForm2);
    }

    /**
     * @test
     * @dataProvider provideCallbackEqualitySamples
     */
    public function testEquals($leftParam, $rightParam, $expected)
    {
        $left = new CallbackStringForm($leftParam);
        $right = new CallbackStringForm($rightParam);

        $expected
            ? static::assertTrue($left->equals($right), "{$left} === {$right}")
            : static::assertFalse($left->equals($right), "{$left} !== {$right}");
    }

    /**
     * @test
     */
    public function testFromStringThrowForMalformedClosure()
    {
        $closure = 'function )';
        $this->expectException(InvalidCallable::class);
        new CallbackStringForm($closure);
    }

    /**
     * @test
     */
    public function testFromStringThrowForMalformedClosureArgs()
    {
        $closure = 'function(...)';
        $this->expectException(InvalidCallable::class);
        new CallbackStringForm($closure);
    }

    /**
     * @return list<array{callable,callable,bool}>
     */
    public function provideCallbackEqualitySamples()
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        return [
            [
                function ($foo) {
                    return $this;
                },
                function ($foo) {
                    return $this;
                },
                true,
            ],
            [
                function ($foo) {
                    return $this;
                },
                'function ($foo)',
                true,
            ],
            [
                function ($foo) {
                    return $this;
                },
                function ($bar) {
                    return $this;
                },
                false,
            ],
            [
                function ($foo) {
                    return $this;
                },
                'function ($bar)',
                false,
            ],
            ['Foo\Bar\baz', 'Foo\\Bar\\baz', true],
            ['Foo\Bar\baz', 'Foo\\Baz\\baz', false],
            [['Foo\Bar\Baz', 'method'], [\Foo\Bar\Baz::class, 'method'], true],
            [['Foo\Bar\Baz', 'method'], [Foo\Bar\Baz::class, 'method'], false],
            [[new \ArrayObject(), 'getArrayCopy'], [new \ArrayObject(), 'getArrayCopy'], true],
            [[new \ArrayObject(), 'getArrayCopy'], 'ArrayObject->getArrayCopy()', true],
            [[new \ArrayObject(), 'getArrayCopy'], 'ArrayObject::getArrayCopy()', false],
        ];
    }
}

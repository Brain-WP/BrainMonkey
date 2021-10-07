<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Name;

use Brain\Monkey\Name\ClosureParamStringForm;
use Brain\Monkey\Name\Exception\InvalidClosureParam;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClosureParamStringFormTest extends UnitTestCase
{

    public function testFromStringThrowsForTooManyParameters()
    {
        $this->expectException(InvalidClosureParam::class);
        $this->expectExceptionCode(InvalidClosureParam::CODE_INVALID_NAME);
        ClosureParamStringForm::fromString('Foo $foo bar');
    }

    public function testFromStringThrowsForBadName()
    {
        $this->expectException(InvalidClosureParam::class);
        $this->expectExceptionCode(InvalidClosureParam::CODE_INVALID_NAME);
        ClosureParamStringForm::fromString('Foo $1foo');
    }

    public function testFromStringThrowsForBadType()
    {
        $this->expectException(InvalidClosureParam::class);
        $this->expectExceptionCode(InvalidClosureParam::CODE_INVALID_TYPE);
        ClosureParamStringForm::fromString('F-oo $foo');
    }

    public function testFromStringVariadic()
    {
        static::assertFalse(ClosureParamStringForm::fromString('Foo $foo')->isVariadic());
        static::assertTrue(ClosureParamStringForm::fromString('...$foo')->isVariadic());
        static::assertTrue(ClosureParamStringForm::fromString(' ... $foo')->isVariadic());
        static::assertTrue(ClosureParamStringForm::fromString('Foo ...$foo')->isVariadic());
        static::assertTrue(ClosureParamStringForm::fromString(' Foo ... $foo ')->isVariadic());
        static::assertTrue(ClosureParamStringForm::fromString('Foo\Bar ...$bar')->isVariadic());
        static::assertTrue(ClosureParamStringForm::fromString(' Foo\Bar ... $bar ')->isVariadic());
        static::assertFalse(ClosureParamStringForm::fromString(' $foo ')->isVariadic());
    }

    public function testFromStringToString()
    {
        $param_a = ClosureParamStringForm::fromString('Foo $foo');
        $param_b = ClosureParamStringForm::fromString('...$foo');
        $param_c = ClosureParamStringForm::fromString(' ... $foo');
        $param_d = ClosureParamStringForm::fromString('Foo ...$foo');
        $param_e = ClosureParamStringForm::fromString(' Foo ... $foo ');
        $param_f = ClosureParamStringForm::fromString('Foo\Bar ...$bar');
        $param_g = ClosureParamStringForm::fromString(' Foo\Bar ... $bar ');
        $param_h = ClosureParamStringForm::fromString(' $foo ');

        static::assertSame('Foo $foo', (string)$param_a);
        static::assertSame('...$foo', (string)$param_b);
        static::assertSame('...$foo', (string)$param_c);
        static::assertSame('Foo ...$foo', (string)$param_d);
        static::assertSame('Foo ...$foo', (string)$param_e);
        static::assertSame('Foo\Bar ...$bar', (string)$param_f);
        static::assertSame('Foo\Bar ...$bar', (string)$param_g);
        static::assertSame('$foo', (string)$param_h);
    }

    public function testFromReflectionToString7()
    {
        if (PHP_MAJOR_VERSION < 7) {
            $this->markTestSkipped('Skipping PHP 7 test.');

            return;
        }

        $param_a = \Mockery::mock(\ReflectionParameter::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_a->shouldReceive('hasType')->andReturn(true);
        if (PHP_VERSION_ID < 70100) {
            // phpcs:ignore PHPCompatibility.Classes.NewClasses.reflectiontypeFound
            $type_a = \Mockery::mock(\ReflectionType::class);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $param_a->shouldReceive('getType')->andReturn($type_a);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $type_a->shouldReceive('__toString')->andReturn('array');
        } else {
            // phpcs:ignore PHPCompatibility.Classes.NewClasses.reflectionnamedtypeFound
            $type_a = \Mockery::mock(\ReflectionNamedType::class);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $param_a->shouldReceive('getType')->andReturn($type_a);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $type_a->shouldReceive('getName')->andReturn('array');
        }
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_a->shouldReceive('getName')->andReturn('foo');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_a->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'array ...$foo',
            (string)ClosureParamStringForm::fromReflectionParameter($param_a)
        );

        $param_b = \Mockery::mock(\ReflectionParameter::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_b->shouldReceive('hasType')->andReturn(true);
        if (PHP_VERSION_ID < 70100) {
            // phpcs:ignore PHPCompatibility.Classes.NewClasses.reflectiontypeFound
            $type_b = \Mockery::mock(\ReflectionType::class);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $param_b->shouldReceive('getType')->andReturn($type_b);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $type_b->shouldReceive('__toString')->andReturn('Foo\\Bar');
        } else {
            // phpcs:ignore PHPCompatibility.Classes.NewClasses.reflectionnamedtypeFound
            $type_b = \Mockery::mock(\ReflectionNamedType::class);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $param_b->shouldReceive('getType')->andReturn($type_b);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $type_b->shouldReceive('getName')->andReturn('Foo\\Bar');
        }
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_b->shouldReceive('getName')->andReturn('bar');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_b->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'Foo\\Bar ...$bar',
            (string)ClosureParamStringForm::fromReflectionParameter($param_b)
        );
    }

    public function testFromReflectionToString5()
    {

        if (PHP_MAJOR_VERSION >= 7) {
            $this->markTestSkipped('Skipping PHP 5.6 test.');

            return;
        }

        $param_a = \Mockery::mock(\ReflectionParameter::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_a->shouldReceive('hasType')->never();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_a->shouldReceive('__toString')->andReturn('Parameter #0 [ <optional> array ...$foo ]');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_a->shouldReceive('getName')->andReturn('foo');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_a->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'array ...$foo',
            (string)ClosureParamStringForm::fromReflectionParameter($param_a)
        );

        $param_b = \Mockery::mock(\ReflectionParameter::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_b->shouldReceive('hasType')->andReturn(true);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_b->shouldReceive('__toString')->andReturn('Parameter #0 [ <optional> Foo\\Bar ...$bar ]');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_b->shouldReceive('getName')->andReturn('bar');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $param_b->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'Foo\\Bar ...$bar',
            (string)ClosureParamStringForm::fromReflectionParameter($param_b)
        );
    }
}

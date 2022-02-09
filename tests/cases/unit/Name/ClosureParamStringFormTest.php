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

use Brain\Monkey\Name\ClosureParamStringForm;
use Brain\Monkey\Name\Exception\InvalidClosureParam;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 *
 * phpcs:disable PHPCompatibility.Classes.NewClasses.reflectiontypeFound
 * phpcs:disable PHPCompatibility.Classes.NewClasses.reflectionnamedtypeFound
 */
class ClosureParamStringFormTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testFromStringThrowsForTooManyParameters()
    {
        $this->expectException(InvalidClosureParam::class);
        $this->expectExceptionCode(InvalidClosureParam::CODE_INVALID_NAME);
        ClosureParamStringForm::fromString('Foo $foo bar');
    }

    /**
     * @test
     */
    public function testFromStringThrowsForBadName()
    {
        $this->expectException(InvalidClosureParam::class);
        $this->expectExceptionCode(InvalidClosureParam::CODE_INVALID_NAME);
        ClosureParamStringForm::fromString('Foo $1foo');
    }

    /**
     * @test
     */
    public function testFromStringThrowsForBadType()
    {
        $this->expectException(InvalidClosureParam::class);
        $this->expectExceptionCode(InvalidClosureParam::CODE_INVALID_TYPE);
        ClosureParamStringForm::fromString('F-oo $foo');
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testFromStringToString()
    {
        $param1 = ClosureParamStringForm::fromString('Foo $foo');
        $param2 = ClosureParamStringForm::fromString('...$foo');
        $param3 = ClosureParamStringForm::fromString(' ... $foo');
        $param4 = ClosureParamStringForm::fromString('Foo ...$foo');
        $param5 = ClosureParamStringForm::fromString(' Foo ... $foo ');
        $param6 = ClosureParamStringForm::fromString('Foo\Bar ...$bar');
        $param7 = ClosureParamStringForm::fromString(' Foo\Bar ... $bar ');
        $param8 = ClosureParamStringForm::fromString(' $foo ');

        static::assertSame('Foo $foo', (string)$param1);
        static::assertSame('...$foo', (string)$param2);
        static::assertSame('...$foo', (string)$param3);
        static::assertSame('Foo ...$foo', (string)$param4);
        static::assertSame('Foo ...$foo', (string)$param5);
        static::assertSame('Foo\Bar ...$bar', (string)$param6);
        static::assertSame('Foo\Bar ...$bar', (string)$param7);
        static::assertSame('$foo', (string)$param8);
    }

    /**
     * @test
     */
    public function testFromReflectionToString7()
    {
        if (PHP_MAJOR_VERSION < 7) {
            $this->markTestSkipped('Skipping PHP 7 test.');
        }

        $param1 = \Mockery::mock(\ReflectionParameter::class);
        $param1->shouldReceive('hasType')->andReturn(true);

        $type1 = (PHP_VERSION_ID < 70100)
            ? \Mockery::mock(\ReflectionType::class)
            : \Mockery::mock(\ReflectionNamedType::class);

        (PHP_VERSION_ID < 70100)
            ? $type1->shouldReceive('__toString')->andReturn('array')
            : $type1->shouldReceive('getName')->andReturn('array');

        $param1->shouldReceive('getType')->andReturn($type1);
        $param1->shouldReceive('getName')->andReturn('foo');
        $param1->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'array ...$foo',
            (string)ClosureParamStringForm::fromReflectionParameter($param1)
        );

        $param2 = \Mockery::mock(\ReflectionParameter::class);
        $param2->shouldReceive('hasType')->andReturn(true);

        $type2 = (PHP_VERSION_ID < 70100)
            ? \Mockery::mock(\ReflectionType::class)
            : \Mockery::mock(\ReflectionNamedType::class);

        (PHP_VERSION_ID < 70100)
            ? $type2->shouldReceive('__toString')->andReturn('Foo\\Bar')
            : $type2->shouldReceive('getName')->andReturn('Foo\\Bar');

        $param2->shouldReceive('getType')->andReturn($type2);
        $param2->shouldReceive('getName')->andReturn('bar');
        $param2->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'Foo\\Bar ...$bar',
            (string)ClosureParamStringForm::fromReflectionParameter($param2)
        );
    }

    /**
     * @test
     */
    public function testFromReflectionToString5()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->markTestSkipped('Skipping PHP 5.6 test.');
        }

        $param1 = \Mockery::mock(\ReflectionParameter::class);
        $param1->shouldReceive('hasType')->never();
        $param1->shouldReceive('__toString')
            ->andReturn('Parameter #0 [ <optional> array ...$foo ]');
        $param1->shouldReceive('getName')->andReturn('foo');
        $param1->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'array ...$foo',
            (string)ClosureParamStringForm::fromReflectionParameter($param1)
        );

        $param2 = \Mockery::mock(\ReflectionParameter::class);
        $param2->shouldReceive('hasType')->andReturn(true);
        $param2->shouldReceive('__toString')
            ->andReturn('Parameter #0 [ <optional> Foo\\Bar ...$bar ]');
        $param2->shouldReceive('getName')->andReturn('bar');
        $param2->shouldReceive('isVariadic')->andReturn(true);

        /** @noinspection PhpParamsInspection */
        static::assertSame(
            'Foo\\Bar ...$bar',
            (string)ClosureParamStringForm::fromReflectionParameter($param2)
        );
    }
}

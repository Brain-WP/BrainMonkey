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

use Brain\Monkey\Name\Exception\InvalidName;
use Brain\Monkey\Name\FunctionName;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionNameTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testConstructorThrowsIfBadName()
    {
        $this->expectException(InvalidName::class);
        $this->expectExceptionCode(InvalidName::CODE_FOR_FUNCTION);
        new FunctionName('fo-o');
    }

    /**
     * @test
     */
    public function testFullQualifiedName()
    {
        $function1 = new FunctionName(__FUNCTION__);
        $function2 = new FunctionName('\Foo\Bar\bazBaz');

        static::assertSame(__FUNCTION__, $function1->fullyQualifiedName());
        static::assertSame('Foo\Bar\bazBaz', $function2->fullyQualifiedName());
    }

    /**
     * @test
     */
    public function testShortName()
    {
        $function1 = new FunctionName(__NAMESPACE__ . '\\' . __FUNCTION__);
        $function2 = new FunctionName('\Foo\Bar\bazBaz');

        static::assertSame(__FUNCTION__, $function1->shortName());
        static::assertSame('bazBaz', $function2->shortName());
    }

    /**
     * @test
     */
    public function testNamespace()
    {
        $function1 = new FunctionName(__NAMESPACE__ . '\\' . __FUNCTION__);
        $function2 = new FunctionName('\Foo\Bar\bazBaz');

        static::assertSame(__NAMESPACE__, $function1->getNamespace());
        static::assertSame('Foo\Bar', $function2->getNamespace());
    }

    /**
     * @test
     */
    public function testEquals()
    {
        $function1 = new FunctionName(__NAMESPACE__ . '\\' . __FUNCTION__);
        $function2 = new FunctionName('\\Brain\\Monkey\\Tests\\Unit\\Name\\testEquals');

        static::assertTrue($function1->equals($function2));
        static::assertTrue($function2->equals($function1));
    }
}

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

use Brain\Monkey\Name\Exception\InvalidName;
use Brain\Monkey\Name\FunctionName;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionNameTest extends UnitTestCase
{

    public function testConstructorThrowsIfBadName()
    {
        $this->expectException(InvalidName::class);
        $this->expectExceptionCode(InvalidName::CODE_FOR_FUNCTION);
        new FunctionName('fo-o');
    }

    public function testFullQualifiedName()
    {
        $function_a = new FunctionName(__FUNCTION__);
        $function_b = new FunctionName('\Foo\Bar\bazBaz');

        static::assertSame(__FUNCTION__, $function_a->fullyQualifiedName());
        static::assertSame('Foo\Bar\bazBaz', $function_b->fullyQualifiedName());
    }

    public function testShortName()
    {
        $function_a = new FunctionName(__NAMESPACE__.'\\'.__FUNCTION__);
        $function_b = new FunctionName('\Foo\Bar\bazBaz');

        static::assertSame(__FUNCTION__, $function_a->shortName());
        static::assertSame('bazBaz', $function_b->shortName());
    }

    public function testNamespace()
    {
        $function_a = new FunctionName(__NAMESPACE__.'\\'.__FUNCTION__);
        $function_b = new FunctionName('\Foo\Bar\bazBaz');

        static::assertSame(__NAMESPACE__, $function_a->getNamespace());
        static::assertSame('Foo\Bar', $function_b->getNamespace());
    }

    public function testEquals()
    {
        $function_a = new FunctionName(__NAMESPACE__.'\\'.__FUNCTION__);
        $function_b = new FunctionName('\\Brain\\Monkey\\Tests\\Unit\\Name\\testEquals');

        static::assertTrue($function_a->equals($function_b));
        static::assertTrue($function_b->equals($function_a));
    }

}
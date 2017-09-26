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

use Brain\Monkey\Name\Exception\InvalidName;
use Brain\Monkey\Name\MethodName;
use Brain\Monkey\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class MethodNameTest extends TestCase
{

    public function testConstructorThrowsIfBadName()
    {
        $this->expectException(InvalidName::class);
        $this->expectExceptionCode(InvalidName::CODE_FOR_METHOD);
        new MethodName('fo-o');
    }

    public function testConstructorThrowsIfNamespacedName()
    {
        $this->expectException(InvalidName::class);
        $this->expectExceptionCode(InvalidName::CODE_FOR_METHOD);
        new MethodName('foo\bar');
    }

    public function testName()
    {
        $method = new MethodName(__FUNCTION__);

        static::assertSame(__FUNCTION__, $method->name());
    }

    public function testEquals()
    {
        static::assertTrue((new MethodName(__FUNCTION__))->equals(new MethodName(__FUNCTION__)));
    }

}
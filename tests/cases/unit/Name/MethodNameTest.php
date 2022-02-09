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
use Brain\Monkey\Name\MethodName;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class MethodNameTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testConstructorThrowsIfBadName()
    {
        $this->expectException(InvalidName::class);
        $this->expectExceptionCode(InvalidName::CODE_FOR_METHOD);
        new MethodName('fo-o');
    }

    /**
     * @test
     */
    public function testConstructorThrowsIfNamespacedName()
    {
        $this->expectException(InvalidName::class);
        $this->expectExceptionCode(InvalidName::CODE_FOR_METHOD);
        new MethodName('foo\bar');
    }

    /**
     * @test
     */
    public function testName()
    {
        $method = new MethodName(__FUNCTION__);

        static::assertSame(__FUNCTION__, $method->name());
    }

    /**
     * @test
     */
    public function testEquals()
    {
        static::assertTrue((new MethodName(__FUNCTION__))->equals(new MethodName(__FUNCTION__)));
    }
}

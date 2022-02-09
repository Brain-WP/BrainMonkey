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

use Brain\Monkey\Name\ClassName;
use Brain\Monkey\Name\Exception\InvalidName;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClassNameTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testConstructorThrowsIfBadName()
    {
        $this->expectException(InvalidName::class);
        new ClassName('Foo Bar');
    }

    /**
     * @test
     */
    public function testFullQualifiedName()
    {
        $class1 = new ClassName(__CLASS__);
        $class2 = new ClassName('\Foo\Bar\Baz');

        static::assertSame(__CLASS__, $class1->fullyQualifiedName());
        static::assertSame('Foo\Bar\Baz', $class2->fullyQualifiedName());
    }

    /**
     * @test
     */
    public function testShortName()
    {
        $class1 = new ClassName(__CLASS__);
        $class2 = new ClassName('\Foo\Bar\Baz');

        static::assertSame('ClassNameTest', $class1->shortName());
        static::assertSame('Baz', $class2->shortName());
    }

    /**
     * @test
     */
    public function testNamespace()
    {
        $class1 = new ClassName(__CLASS__);
        $class2 = new ClassName('\Foo\Bar\Baz');

        static::assertSame(__NAMESPACE__, $class1->getNamespace());
        static::assertSame('Foo\Bar', $class2->getNamespace());
    }

    /**
     * @test
     */
    public function testEquals()
    {
        $class1 = new ClassName(__CLASS__);
        $class2 = new ClassName(ClassNameTest::class);

        static::assertTrue($class1->equals($class2));
        static::assertTrue($class2->equals($class1));
    }
}

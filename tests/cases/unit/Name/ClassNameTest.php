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

use Brain\Monkey\Name\ClassName;
use Brain\Monkey\Name\Exception\InvalidName;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClassNameTest extends UnitTestCase
{

    public function testConstructorThrowsIfBadName()
    {
        $this->expectException(InvalidName::class);
        new ClassName('Foo Bar');
    }

    public function testFullQualifiedName()
    {
        $class_a = new ClassName(__CLASS__);
        $class_b = new ClassName('\Foo\Bar\Baz');

        static::assertSame(__CLASS__, $class_a->fullyQualifiedName());
        static::assertSame('Foo\Bar\Baz', $class_b->fullyQualifiedName());
    }

    public function testShortName()
    {
        $class_a = new ClassName(__CLASS__);
        $class_b = new ClassName('\Foo\Bar\Baz');

        static::assertSame('ClassNameTest', $class_a->shortName());
        static::assertSame('Baz', $class_b->shortName());
    }

    public function testNamespace()
    {
        $class_a = new ClassName(__CLASS__);
        $class_b = new ClassName('\Foo\Bar\Baz');

        static::assertSame(__NAMESPACE__, $class_a->getNamespace());
        static::assertSame('Foo\Bar', $class_b->getNamespace());
    }

    public function testEquals()
    {
        $class_a = new ClassName(__CLASS__);
        $class_b = new ClassName(ClassNameTest::class);
        
        static::assertTrue($class_a->equals($class_b));
        static::assertTrue($class_b->equals($class_a));
    }

}
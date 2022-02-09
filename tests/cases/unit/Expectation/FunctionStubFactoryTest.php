<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation;

use Brain\Monkey\Expectation\Exception\Exception;
use Brain\Monkey\Expectation\FunctionStubFactory;
use Brain\Monkey\Name\FunctionName;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionStubFactoryTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testCreateReturnSameStubWhenCalledMoreTimes()
    {
        $factory = new FunctionStubFactory();

        $stub1 = $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);
        $stub2 = $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);

        static::assertSame($stub1, $stub2);
    }

    /**
     * @test
     */
    public function testCreateThrownWhenCalledWithSameNameButDifferentScopes()
    {
        $factory = new FunctionStubFactory();
        $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);

        $this->expectException(Exception::class);

        $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_EXPECTATION);
    }

    /**
     * @test
     */
    public function testHas()
    {
        $factory = new FunctionStubFactory();
        $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);

        static::assertTrue($factory->has(new FunctionName('a')));
        static::assertFalse($factory->has(new FunctionName('a\a')));
    }

    /**
     * @test
     */
    public function testHasAndReset()
    {
        $factory = new FunctionStubFactory();
        $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);

        $name = new FunctionName('a');

        static::assertTrue($factory->has($name));
        $factory->reset();
        static::assertFalse($factory->has($name));
    }
}

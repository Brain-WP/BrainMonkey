<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation;

use Brain\Monkey\Expectation\Exception\Exception;
use Brain\Monkey\Expectation\FunctionStubFactory;
use Brain\Monkey\Name\FunctionName;
use Brain\Monkey\Tests\UnitTestCase;

class FunctionStubFactoryTest extends UnitTestCase
{

    public function testCreateReturnSameStubWhenCalledMoreTimes()
    {
        $factory = new FunctionStubFactory();

        $stub_a = $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);
        $stub_b = $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);

        static::assertSame($stub_a, $stub_b);
    }

    public function testCreateThrownWhenCalledWithSameNameButDifferentScopes()
    {

        $factory = new FunctionStubFactory();
        $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);

        $this->expectException(Exception::class);

        $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_EXPECTATION);
    }

    public function testHas()
    {
        $factory = new FunctionStubFactory();
        $factory->create(new FunctionName('a'), FunctionStubFactory::SCOPE_STUB);

        static::assertTrue($factory->has(new FunctionName('a')));
        static::assertFalse($factory->has(new FunctionName('a\a')));
    }

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

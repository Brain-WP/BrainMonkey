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

use Brain\Monkey\Expectation\Expectation;
use Brain\Monkey\Expectation\ExpectationFactory;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\UnitTestCase;
use Mockery\Exception\InvalidCountException;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExpectationFactoryTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testForFunctionExecuted()
    {
        $factory = new ExpectationFactory();

        $expectationA = $factory->forFunctionExecuted('test');
        $expectationC = $factory->forFunctionExecuted('test_x');

        $mockA = $expectationA->mockeryExpectation()->getMock();
        $mockB = $expectationC->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mockA->test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mockB->test_x();

        static::assertInstanceOf(Expectation::class, $expectationA);
        static::assertInstanceOf(Expectation::class, $expectationC);
        static::assertNotSame($expectationA, $expectationC);
        static::assertNotSame($mockA, $mockB);
    }

    /**
     * @test
     */
    public function testForActionAdded()
    {
        $factory = new ExpectationFactory();

        $expectationA = $factory->forActionAdded('test');
        $expectationC = $factory->forActionAdded('test_x');

        $mockA = $expectationA->mockeryExpectation()->getMock();
        $mockB = $expectationC->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mockA->add_action_test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mockB->add_action_test_x();

        static::assertInstanceOf(Expectation::class, $expectationA);
        static::assertInstanceOf(Expectation::class, $expectationC);
        static::assertNotSame($expectationA, $expectationC);
        static::assertNotSame($mockA, $mockB);
    }

    /**
     * @test
     */
    public function testForActionDone()
    {
        $factory = new ExpectationFactory();

        $expectationA = $factory->forActionDone('test');
        $expectationC = $factory->forActionDone('test_x');

        $mockA = $expectationA->mockeryExpectation()->getMock();
        $mockB = $expectationC->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mockA->do_action_test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mockB->do_action_test_x();

        static::assertInstanceOf(Expectation::class, $expectationA);
        static::assertInstanceOf(Expectation::class, $expectationC);
        static::assertNotSame($expectationA, $expectationC);
        static::assertNotSame($mockA, $mockB);
    }

    /**
     * @test
     */
    public function testForFilterAdded()
    {
        $factory = new ExpectationFactory();

        $expectationA = $factory->forFilterAdded('test');
        $expectationC = $factory->forFilterAdded('test_x');

        $mockA = $expectationA->mockeryExpectation()->getMock();
        $mockB = $expectationC->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mockA->add_filter_test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mockB->add_filter_test_x();

        static::assertInstanceOf(Expectation::class, $expectationA);
        static::assertInstanceOf(Expectation::class, $expectationC);
        static::assertNotSame($expectationA, $expectationC);
        static::assertNotSame($mockA, $mockB);
    }

    /**
     * @test
     */
    public function testForFilterApplied()
    {
        $factory = new ExpectationFactory();

        $expectationA = $factory->forFilterApplied('test');
        $expectationC = $factory->forFilterApplied('test_x');

        $mockA = $expectationA->mockeryExpectation()->getMock();
        $mockB = $expectationC->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mockA->apply_filters_test('x');
        /** @noinspection PhpUndefinedMethodInspection */
        $mockB->apply_filters_test_x('x');

        static::assertInstanceOf(Expectation::class, $expectationA);
        static::assertInstanceOf(Expectation::class, $expectationC);
        static::assertNotSame($expectationA, $expectationC);
        static::assertNotSame($mockA, $mockB);
    }

    /**
     * @test
     */
    public function testMockFor()
    {
        $this->expectMockeryException(InvalidCountException::class);

        $factory = new ExpectationFactory();

        $targetA = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, 'foo');
        $targetB = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_APPLIED, 'foo');

        static::assertFalse($factory->hasMockFor($targetA));
        static::assertFalse($factory->hasMockFor($targetB));

        $factory->forFilterAdded('foo');
        $factory->forFilterApplied('foo');

        static::assertTrue($factory->hasMockFor(clone $targetA));
        static::assertTrue($factory->hasMockFor(clone $targetB));

        $mockA = $factory->mockFor($targetA);
        $mockB = $factory->mockFor($targetB);

        static::assertNotSame($mockA, $mockB);

        $mockA2 = $factory->mockFor($targetA);
        $mockB2 = $factory->mockFor($targetB);

        static::assertSame($mockA, $mockA2);
        static::assertSame($mockB, $mockB2);
    }
}

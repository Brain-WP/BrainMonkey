<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Expectation;

use Brain\Monkey\Expectation\Expectation;
use Brain\Monkey\Expectation\ExpectationFactory;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\TestCase;
use Mockery\Exception\InvalidCountException;

class ExpectationFactoryTest extends TestCase
{

    public function testForFunctionExecuted()
    {
        $factory = new ExpectationFactory();

        $expectation_a = $factory->forFunctionExecuted('test');
        $expectation_c = $factory->forFunctionExecuted('test_x');

        $mock_a = $expectation_a->mockeryExpectation()->getMock();
        $mock_b = $expectation_c->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mock_a->test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mock_b->test_x();

        static::assertInstanceOf(Expectation::class, $expectation_a);
        static::assertInstanceOf(Expectation::class, $expectation_c);
        static::assertNotSame($expectation_a, $expectation_c);
        static::assertNotSame($mock_a, $mock_b);
    }

    public function testForActionAdded()
    {
        $factory = new ExpectationFactory();

        $expectation_a = $factory->forActionAdded('test');
        $expectation_c = $factory->forActionAdded('test_x');

        $mock_a = $expectation_a->mockeryExpectation()->getMock();
        $mock_b = $expectation_c->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mock_a->add_action_test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mock_b->add_action_test_x();

        static::assertInstanceOf(Expectation::class, $expectation_a);
        static::assertInstanceOf(Expectation::class, $expectation_c);
        static::assertNotSame($expectation_a, $expectation_c);
        static::assertNotSame($mock_a, $mock_b);
    }

    public function testForActionDone()
    {
        $factory = new ExpectationFactory();

        $expectation_a = $factory->forActionDone('test');
        $expectation_c = $factory->forActionDone('test_x');

        $mock_a = $expectation_a->mockeryExpectation()->getMock();
        $mock_b = $expectation_c->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mock_a->do_action_test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mock_b->do_action_test_x();

        static::assertInstanceOf(Expectation::class, $expectation_a);
        static::assertInstanceOf(Expectation::class, $expectation_c);
        static::assertNotSame($expectation_a, $expectation_c);
        static::assertNotSame($mock_a, $mock_b);
    }

    public function testForFilterAdded()
    {
        $factory = new ExpectationFactory();

        $expectation_a = $factory->forFilterAdded('test');
        $expectation_c = $factory->forFilterAdded('test_x');

        $mock_a = $expectation_a->mockeryExpectation()->getMock();
        $mock_b = $expectation_c->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mock_a->add_filter_test();
        /** @noinspection PhpUndefinedMethodInspection */
        $mock_b->add_filter_test_x();

        static::assertInstanceOf(Expectation::class, $expectation_a);
        static::assertInstanceOf(Expectation::class, $expectation_c);
        static::assertNotSame($expectation_a, $expectation_c);
        static::assertNotSame($mock_a, $mock_b);
    }

    public function testForFilterApplied()
    {
        $factory = new ExpectationFactory();

        $expectation_a = $factory->forFilterApplied('test');
        $expectation_c = $factory->forFilterApplied('test_x');

        $mock_a = $expectation_a->mockeryExpectation()->getMock();
        $mock_b = $expectation_c->mockeryExpectation()->getMock();

        /** @noinspection PhpUndefinedMethodInspection */
        $mock_a->apply_filters_test('x');
        /** @noinspection PhpUndefinedMethodInspection */
        $mock_b->apply_filters_test_x('x');

        static::assertInstanceOf(Expectation::class, $expectation_a);
        static::assertInstanceOf(Expectation::class, $expectation_c);
        static::assertNotSame($expectation_a, $expectation_c);
        static::assertNotSame($mock_a, $mock_b);
    }

    public function testMockFor()
    {
        $this->expectMockeryException(InvalidCountException::class);

        $factory = new ExpectationFactory();

        $target_a = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, 'foo');
        $target_b = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_APPLIED, 'foo');

        static::assertFalse($factory->hasMockFor($target_a));
        static::assertFalse($factory->hasMockFor($target_b));

        $factory->forFilterAdded('foo');
        $factory->forFilterApplied('foo');

        static::assertTrue($factory->hasMockFor(clone $target_a));
        static::assertTrue($factory->hasMockFor(clone $target_b));

        $mock_a = $factory->mockFor($target_a);
        $mock_b = $factory->mockFor($target_b);

        static::assertNotSame($mock_a, $mock_b);

        $mock_a_1 = $factory->mockFor($target_a);
        $mock_b_1 = $factory->mockFor($target_b);

        static::assertSame($mock_a, $mock_a_1);
        static::assertSame($mock_b, $mock_b_1);
    }

}

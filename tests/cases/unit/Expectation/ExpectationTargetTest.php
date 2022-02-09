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

use Brain\Monkey\Exception;
use Brain\Monkey\Expectation\Exception\InvalidExpectationName;
use Brain\Monkey\Expectation\Exception\InvalidExpectationType;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExpectationTargetTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testConstructorThrowExceptionIfInvalidType()
    {
        $this->expectException(InvalidExpectationType::class);
        new ExpectationTarget('foo', 'bar');
    }

    /**
     * @test
     */
    public function testConstructorThrowExceptionIfInvalidName()
    {
        $this->expectException(InvalidExpectationName::class);
        new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 1);
    }

    /**
     * @test
     */
    public function testConstructorThrowExceptionIfInvalidFunctionName()
    {
        $this->expectException(Exception::class);
        new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo.bar');
    }

    /**
     * @test
     */
    public function testIdentifierEqualsNoMatterInstance()
    {
        $target1 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target2 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target3 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');
        $target4 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');

        static::assertSame($target1->identifier(), $target2->identifier());
        static::assertSame($target3->identifier(), $target4->identifier());
        static::assertNotEquals($target1->identifier(), $target3->identifier());
    }

    /**
     * @test
     */
    public function testMockMethodName()
    {
        $target1 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo.bar');
        $target2 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_DONE, 'foo.bar');
        $target3 = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, 'foo.bar');
        $target4 = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_APPLIED, 'foo.bar');

        static::assertSame($target1->mockMethodName(), 'add_action_' . $target1->name());
        static::assertSame(0, substr_count($target1->mockMethodName(), '.'));

        static::assertSame($target2->mockMethodName(), 'do_action_' . $target2->name());
        static::assertSame(0, substr_count($target2->mockMethodName(), '.'));

        static::assertSame($target3->mockMethodName(), 'add_filter_' . $target3->name());
        static::assertSame(0, substr_count($target3->mockMethodName(), '.'));

        static::assertSame($target4->mockMethodName(), 'apply_filters_' . $target4->name());
        static::assertSame(0, substr_count($target4->mockMethodName(), '.'));
    }

    /**
     * @test
     */
    public function testEquals()
    {
        $target1 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target2 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo\foo');
        $target3 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target4 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, '_foo');
        $target5 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');

        static::assertFalse($target1->equals($target2));
        static::assertTrue($target1->equals($target3));
        static::assertFalse($target1->equals($target4));
        static::assertFalse($target1->equals($target5));
    }
}

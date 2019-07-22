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

use Brain\Monkey\Exception;
use Brain\Monkey\Expectation\Exception\InvalidExpectationName;
use Brain\Monkey\Expectation\Exception\InvalidExpectationType;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\UnitTestCase;

class ExpectationTargetTest extends UnitTestCase
{

    public function testConstructorThrowExceptionIfInvalidType()
    {
        $this->expectException(InvalidExpectationType::class);
        new ExpectationTarget('foo', 'bar');
    }

    public function testConstructorThrowExceptionIfInvalidName()
    {
        $this->expectException(InvalidExpectationName::class);
        new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 1);
    }

    public function testConstructorThrowExceptionIfInvalidFunctionName()
    {
        $this->expectException(Exception::class);
        new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo.bar');
    }

    public function testIdentifierEqualsNoMatterInstance()
    {
        $target_1 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target_2 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target_3 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');
        $target_4 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');

        static::assertSame($target_1->identifier(), $target_2->identifier());
        static::assertSame($target_3->identifier(), $target_4->identifier());
        static::assertNotEquals($target_1->identifier(), $target_3->identifier());
    }

    public function testMockMethodName()
    {
        $target_1 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo.bar');
        $target_2 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_DONE, 'foo.bar');
        $target_3 = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, 'foo.bar');
        $target_4 = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_APPLIED, 'foo.bar');

        static::assertSame($target_1->mockMethodName(), 'add_action_'.$target_1->name());
        static::assertSame(0, substr_count($target_1->mockMethodName(), '.'));

        static::assertSame($target_2->mockMethodName(), 'do_action_'.$target_2->name());
        static::assertSame(0, substr_count($target_2->mockMethodName(), '.'));

        static::assertSame($target_3->mockMethodName(), 'add_filter_'.$target_3->name());
        static::assertSame(0, substr_count($target_3->mockMethodName(), '.'));

        static::assertSame($target_4->mockMethodName(), 'apply_filters_'.$target_4->name());
        static::assertSame(0, substr_count($target_4->mockMethodName(), '.'));
    }

    public function testEquals()
    {
        $target_1 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target_2 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo\foo');
        $target_3 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, 'foo');
        $target_4 = new ExpectationTarget(ExpectationTarget::TYPE_FUNCTION, '_foo');
        $target_5 = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');

        static::assertFalse($target_1->equals($target_2));
        static::assertTrue($target_1->equals($target_3));
        static::assertFalse($target_1->equals($target_4));
        static::assertFalse($target_1->equals($target_5));
    }

}

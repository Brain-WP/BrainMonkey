<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Api;

use Brain\Monkey;
use Mockery\Exception\InvalidCountException;
use Mockery\Exception\InvalidOrderException;

/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package Brain\Monkey\Tests
 */
class RemoveFilterTest extends Monkey\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function testRemoveFilterNotAdded()
    {
        static::assertFalse(remove_filter('the_title', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithDifferentCallback()
    {
        add_filter('the_title', 'one_callback');

        static::assertFalse(remove_filter('the_title', 'another_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackDifferentPriority()
    {
        add_filter('the_title', 'my_callback', 10);

        static::assertFalse(remove_filter('the_title', 'my_callback', 20));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackDifferentPriorityBecauseDefaultOnAdd()
    {
        add_filter('the_title', 'my_callback');

        static::assertFalse(remove_filter('the_title', 'my_callback', 20));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackDifferentPriorityBecauseDefaultOnRemove()
    {
        add_filter('the_title', 'my_callback', 20);

        static::assertFalse(remove_filter('the_title', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriority()
    {
        add_filter('the_title', 'my_callback', 30);

        static::assertTrue(remove_filter('the_title', 'my_callback', 30));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriorityBecauseDefaultOnAdd()
    {
        add_filter('the_title', 'my_callback');

        static::assertTrue(remove_filter('the_title', 'my_callback', 10));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriorityBecauseDefaultOnRemove()
    {
        add_filter('the_title', 'my_callback', 10);

        static::assertTrue(remove_filter('the_title', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriorityBecauseDefault()
    {
        add_filter('the_title', 'my_callback');

        static::assertTrue(remove_filter('the_title', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveAssertionFailedWithNoCallbackAndNoPriority()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Filters\expectRemoved('the_title');
    }

    /**
     * @test
     */
    public function testRemoveAssertionFailedWithNoPriority()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Filters\expectRemoved('the_title')->with('my_callback');
    }

    /**
     * @test
     */
    public function testRemoveAssertionFailedWrongCount()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Filters\expectRemoved('the_title')->twice()->with('my_callback');

        remove_filter('the_title', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveAssertionSuccessWithNoPriority()
    {
        Monkey\Filters\expectRemoved('the_title')->twice()->with('my_callback');

        remove_filter('the_title', 'my_callback');
        remove_filter('the_title', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveManyAssertionSuccessWithDifferentPriority()
    {
        Monkey\Filters\expectRemoved('the_title')
            ->once()
            ->with('my_callback')
            ->andAlsoExpectIt()
            ->once()
            ->with('my_callback', 42);

        remove_filter('the_title', 'my_callback', 42);
        remove_filter('the_title', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveManyAssertionFailsWithDifferentPriorityOrdered()
    {
        $this->expectException(InvalidOrderException::class);
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Filters\expectRemoved('the_title')
            ->once()
            ->with('my_callback')
            ->ordered()
            ->andAlsoExpectIt()
            ->once()
            ->with('my_callback', 42)
            ->ordered();

        remove_filter('the_title', 'my_callback', 42);
        remove_filter('the_title', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveManyAssertionSuccessWithDifferentPriorityOrdered()
    {
        Monkey\Filters\expectRemoved('the_title')
            ->once()
            ->with('my_callback')
            ->ordered()
            ->andAlsoExpectIt()
            ->once()
            ->with('my_callback', 42)
            ->ordered();

        remove_filter('the_title', 'my_callback');
        remove_filter('the_title', 'my_callback', 42);
    }

    /**
     * @test
     */
    public function testRemoveManyAssertionSuccessWithDifferentCallbacksAndPriorities()
    {
        $cb1 = static function () {
            return 1;
        };

        $cb2 = static function ($value) {
            return $value;
        };

        Monkey\Filters\expectRemoved('my_hook')
            ->once()
            ->with($cb1, 10);

        Monkey\Filters\expectRemoved('my_hook')
            ->once()
            ->with($cb2, 22);

        add_filter('my_hook', $cb1);
        add_filter('my_hook', $cb2, 22);

        static::assertTrue(remove_filter('my_hook', $cb1));
        static::assertTrue(remove_filter('my_hook', $cb2, 22));
        static::assertFalse(remove_filter('my_hook', $cb2));
    }
}

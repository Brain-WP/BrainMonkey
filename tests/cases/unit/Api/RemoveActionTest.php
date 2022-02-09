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
class RemoveActionTest extends Monkey\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function testRemoveActionNotAdded()
    {
        static::assertFalse(remove_action('init', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithDifferentCallback()
    {
        add_action('init', 'one_callback');

        static::assertFalse(remove_action('init', 'another_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackDifferentPriority()
    {
        add_action('init', 'my_callback', 10);

        static::assertFalse(remove_action('init', 'my_callback', 20));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackDifferentPriorityBecauseDefaultOnAdd()
    {
        add_action('init', 'my_callback');

        static::assertFalse(remove_action('init', 'my_callback', 20));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackDifferentPriorityBecauseDefaultOnRemove()
    {
        add_action('init', 'my_callback', 20);

        static::assertFalse(remove_action('init', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriority()
    {
        add_action('init', 'my_callback', 30);

        static::assertTrue(remove_action('init', 'my_callback', 30));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriorityBecauseDefaultOnAdd()
    {
        add_action('init', 'my_callback');

        static::assertTrue(remove_action('init', 'my_callback', 10));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriorityBecauseDefaultOnRemove()
    {
        add_action('init', 'my_callback', 10);

        static::assertTrue(remove_action('init', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveActionAddedWithSameCallbackAndSamePriorityBecauseDefault()
    {
        add_action('init', 'my_callback');

        static::assertTrue(remove_action('init', 'my_callback'));
    }

    /**
     * @test
     */
    public function testRemoveAssertionFailedWithNoCallbackAndNoPriority()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Actions\expectRemoved('init');
    }

    /**
     * @test
     */
    public function testRemoveAssertionFailedWithNoPriority()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Actions\expectRemoved('init')->with('my_callback');
    }

    /**
     * @test
     */
    public function testRemoveAssertionFailedWrongCount()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Actions\expectRemoved('init')->twice()->with('my_callback');

        remove_action('init', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveAssertionSuccessWithNoPriority()
    {
        Monkey\Actions\expectRemoved('init')->twice()->with('my_callback');

        remove_action('init', 'my_callback');
        remove_action('init', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveManyAssertionSuccessWithDifferentPriority()
    {
        Monkey\Actions\expectRemoved('init')
            ->once()
            ->with('my_callback')
            ->andAlsoExpectIt()
            ->once()
            ->with('my_callback', 42);

        remove_action('init', 'my_callback', 42);
        remove_action('init', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveManyAssertionFailsWithDifferentPriorityOrdered()
    {
        $this->expectException(InvalidOrderException::class);
        $this->expectMockeryException(InvalidCountException::class);

        Monkey\Actions\expectRemoved('init')
            ->once()
            ->with('my_callback')
            ->ordered()
            ->andAlsoExpectIt()
            ->once()
            ->with('my_callback', 42)
            ->ordered();

        remove_action('init', 'my_callback', 42);
        remove_action('init', 'my_callback');
    }

    /**
     * @test
     */
    public function testRemoveManyAssertionSuccessWithDifferentPriorityOrdered()
    {
        Monkey\Actions\expectRemoved('init')
            ->once()
            ->with('my_callback')
            ->ordered()
            ->andAlsoExpectIt()
            ->once()
            ->with('my_callback', 42)
            ->ordered();

        remove_action('init', 'my_callback');
        remove_action('init', 'my_callback', 42);
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

        Monkey\Actions\expectRemoved('my_hook')
            ->once()
            ->with($cb1, 10);

        Monkey\Actions\expectRemoved('my_hook')
            ->once()
            ->with($cb2, 22);

        add_action('my_hook', $cb1);
        add_action('my_hook', $cb2, 22);

        static::assertTrue(remove_action('my_hook', $cb1));
        static::assertTrue(remove_action('my_hook', $cb2, 22));
        static::assertFalse(remove_action('my_hook', $cb2));
    }
}

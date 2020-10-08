<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Api;

use Brain\Monkey\Filters;
use Brain\Monkey\Tests\UnitTestCase;
use Mockery\Exception\InvalidCountException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class AddFiltersTest extends UnitTestCase
{

    public function testAddNull()
    {
        add_filter('the_title', 'strtolower', 20, 1);
        // just want to see that when called properly nothing bad happen
        static::assertTrue(true);
    }

    public function testAddAndHas()
    {
        add_filter('the_title', 'strtolower', 30, 1);
        add_filter('the_title', function (array $title) {
            return true;
        });
        add_filter('the_title', [$this, __FUNCTION__], 20);

        static::assertEquals(30, has_filter('the_title', 'strtolower'));
        static::assertEquals(10, has_filter('the_title', 'function(array $title)'));
        static::assertEquals(20, has_filter('the_title', __CLASS__.'->'.__FUNCTION__.'()'));

        static::assertFalse(has_filter('the_content', 'strtolower'));
        static::assertFalse(has_filter('foo', 'function()'));
        static::assertFalse(has_filter('bar', __CLASS__.'->'.__FUNCTION__.'()'));
    }

    public function testHasWithoutCallback()
    {
        static::assertFalse(has_filter('the_title'));
        add_filter('the_title', 'strtolower', 30, 1);
        static::assertTrue(has_filter('the_title'));
    }

    public function testExpectAdded()
    {
        Filters\expectAdded('the_title')->times(3)->with(
            \Mockery::anyOf('strtolower', 'strtoupper', [$this, __FUNCTION__]),
            \Mockery::type('int')
        );
        Filters\expectAdded('foo')->never();
        Filters\expectAdded('the_content')->once();
        Filters\expectAdded('the_excerpt')
            ->once()
            ->with(
                \Mockery::on(function ($callback) {
                    return
                        is_array($callback)
                        && is_a($callback[0], __CLASS__)
                        && $callback[1] === 'testExpectAdded';
                }),
                30
            );

        add_filter('the_title', 'strtolower', 30);
        add_filter('the_title', 'strtoupper', 20);
        add_filter('the_title', [$this, __FUNCTION__], 20);
        add_filter('the_content', function () {
            return 'baz';
        });
        add_filter('the_excerpt', [$this, __FUNCTION__], 30);
    }

    public function testAddedSameFilterDifferentArguments()
    {
        Filters\expectAdded('double_filter')
            ->once()
            ->ordered()
            ->with('__return_true', 10);

        Filters\expectAdded('double_filter')
            ->once()
            ->ordered()
            ->with('__return_false', 20);

        add_filter('double_filter', '__return_true', 10);
        add_filter('double_filter', '__return_false', 20);
    }

    public function testRemoveAction()
    {
        Filters\expectAdded('the_title')->once();

        add_filter('the_title', '__return_empty_string', 20);

        static::assertEquals(20, has_filter('the_title', '__return_empty_string'));

        remove_filter('the_title', '__return_empty_string', 20);

        static::assertFalse(has_filter('the_title', '__return_empty_string'));
    }

    public function testAddActionWhenHappen()
    {
        Filters\expectAdded('foo')->once()->whenHappen(function ($callable, $priority, $args) {
            $callable();
            static::assertSame(20, $priority);
            static::assertSame(2, $args);
        });

        $this->expectOutputString('Foo!');

        add_filter('foo', function () {
            echo 'Foo!';
        }, 20, 2);
    }

    public function testAndAlsoExpect()
    {
        Filters\expectAdded('a_filter')
            ->once()
            ->ordered()
            ->with('__return_true', 10)
            ->andAlsoExpectIt()
            ->once()
            ->ordered()
            ->with('__return_false', 20);

        add_filter('a_filter', '__return_true', 10);
        add_filter('a_filter', '__return_false', 20);
    }

    public function testExpectWithNoArgsFailsIfNotAdded()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Filters\expectAdded('the_title');
    }
}

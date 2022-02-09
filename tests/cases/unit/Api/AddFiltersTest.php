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

use Brain\Monkey\Filters;
use Brain\Monkey\Tests\UnitTestCase;
use Mockery\Exception\InvalidCountException;

/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package Brain\Monkey\Tests
 */
class AddFiltersTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testAddNull()
    {
        add_filter('the_title', 'strtolower', 20, 1);
        // just want to see that when called properly nothing bad happen
        static::assertTrue(true);
    }

    /**
     * @test
     */
    public function testAddAndHas()
    {
        add_filter('the_title', 'strtolower', 30, 1);
        add_filter(
            'the_title',
            static function (array $title) {
                return true;
            }
        );
        add_filter('the_title', [$this, __FUNCTION__], 20);

        static::assertSame(30, has_filter('the_title', 'strtolower'));
        static::assertSame(10, has_filter('the_title', 'static function(array $title)'));
        static::assertSame(20, has_filter('the_title', __CLASS__ . '->' . __FUNCTION__ . '()'));

        static::assertFalse(has_filter('the_content', 'strtolower'));
        static::assertFalse(has_filter('foo', 'function()'));
        static::assertFalse(has_filter('bar', __CLASS__ . '->' . __FUNCTION__ . '()'));
    }

    /**
     * @test
     */
    public function testHasWithoutCallback()
    {
        static::assertFalse(has_filter('the_title'));
        add_filter('the_title', 'strtolower', 30, 1);
        static::assertTrue(has_filter('the_title'));
    }

    /**
     * @test
     */
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
                \Mockery::on(
                    static function ($callback) {
                        return
                            is_array($callback)
                            && is_a($callback[0], __CLASS__)
                            && $callback[1] === 'testExpectAdded';
                    }
                ),
                30
            );

        add_filter('the_title', 'strtolower', 30);
        add_filter('the_title', 'strtoupper', 20);
        add_filter('the_title', [$this, __FUNCTION__], 20);
        add_filter(
            'the_content',
            static function () {
                return 'baz';
            }
        );
        add_filter('the_excerpt', [$this, __FUNCTION__], 30);
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testRemoveAction()
    {
        Filters\expectAdded('the_title')->once();

        add_filter('the_title', '__return_empty_string', 20);

        static::assertSame(20, has_filter('the_title', '__return_empty_string'));

        remove_filter('the_title', '__return_empty_string', 20);

        static::assertFalse(has_filter('the_title', '__return_empty_string'));
    }

    /**
     * @test
     */
    public function testAddActionWhenHappen()
    {
        Filters\expectAdded('foo')->once()->whenHappen(
            static function ($callable, $priority, $args) {
                $callable();
                static::assertSame(20, $priority);
                static::assertSame(2, $args);
            }
        );

        $this->expectOutputString('Foo!');

        add_filter(
            'foo',
            static function () {
                echo 'Foo!';

                return 1;
            },
            20,
            2
        );
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testExpectWithNoArgsFailsIfNotAdded()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Filters\expectAdded('the_title');
    }
}

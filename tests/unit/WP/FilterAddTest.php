<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\WP;

use PHPUnit_Framework_TestCase;
use Brain\Monkey;
use Brain\Monkey\WP\Filters;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class FilterAddTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Monkey::tearDownWP();
        parent::tearDown();
    }

    public function testAddNull()
    {
        add_filter('the_title', 'strtolower', 20, 1);
        // just want to see that when called properly nothing bad happen
        static::assertTrue(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadHook()
    {
        add_filter(true, 'the_title', 20, 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadCallback()
    {
        add_filter('the_title', 'meeeeeeeeee', 20, 2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadPriority()
    {
        add_filter('the_title', 'strtolower', 'early', 2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadNumArgs()
    {
        add_filter('the_title', 'strtolower', 30, 'two');
    }

    public function testAddAndHas()
    {
        add_filter('the_title', 'strtolower', 30, 1);
        add_filter('the_title', function () {
            return true;
        });
        add_filter('the_title', [$this, __FUNCTION__], 20);

        static::assertTrue(has_filter('the_title', 'strtolower', 30, 1));
        static::assertTrue(has_filter('the_title', 'function()'));
        static::assertTrue(has_filter('the_title', get_class($this).'->'.__FUNCTION__.'()', 20));

        static::assertTrue(has_filter('the_title', 'strtolower', 30));
        static::assertTrue(has_filter('the_title', 'function()', 10, 1));
        static::assertTrue(has_filter('the_title', get_class($this).'->'.__FUNCTION__.'()', 20, 1));

        static::assertFalse(has_filter('the_title', 'strtolower'));
        static::assertFalse(has_filter('the_title', 'function()', 10, 3));
        static::assertFalse(has_filter('the_title', get_class($this).'->'.__FUNCTION__.'()'));

        static::assertFalse(has_filter('the_content', 'strtolower', 30, 1));
        static::assertFalse(has_filter('foo', 'function()'));
        static::assertFalse(has_filter('bar', get_class($this).'->'.__FUNCTION__.'()', 20));
    }

    public function testAddAndHasWithMethods()
    {
        add_filter('the_title', 'strtolower', 30, 1);
        add_filter('the_title', function () {
            return true;
        });
        add_filter('the_title', [$this, __FUNCTION__], 20);

        $name = get_class($this).'->'.__FUNCTION__.'()';

        static::assertTrue(Monkey::filters()->has('the_title', 'strtolower', 30, 1));
        static::assertTrue(Monkey::filters()->has('the_title', 'function()'));
        static::assertTrue(Monkey::filters()->has('the_title', $name, 20));

        static::assertTrue(Monkey::filters()->has('the_title', 'strtolower', 30));
        static::assertTrue(Monkey::filters()->has('the_title', 'function()', 10, 1));
        static::assertTrue(Monkey::filters()->has('the_title', $name, 20));

        static::assertFalse(Monkey::filters()->has('the_title', 'strtolower'));
        static::assertFalse(Monkey::filters()->has('the_title', 'function()', 10, 3));
        static::assertFalse(Monkey::filters()->has('the_title', $name));

        static::assertFalse(Monkey::filters()->has('the_content', 'strtolower', 30, 1));
        static::assertFalse(Monkey::filters()->has('foo', 'function()'));
        static::assertFalse(Monkey::filters()->has('bar', $name, 20));
    }

    public function testAddWithoutCallback()
    {
        static::assertFalse(Monkey::filters()->has('the_title'));
        add_filter('the_title', 'strtolower', 30, 1);
        static::assertTrue(Monkey::filters()->has('the_title'));
    }

    public function testExpectAdded()
    {
        Filters::expectAdded('the_title')->times(3)->with(
            Mockery::anyOf('strtolower', 'strtoupper', [$this, __FUNCTION__]),
            Mockery::type('int')
        );
        Filters::expectAdded('foo')->never();
        Filters::expectAdded('the_content')->once();
        Filters::expectAdded('the_excerpt')
               ->once()
               ->with(
                   Mockery::on(function ($callback) {
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
        $f1 = function () {
        };

        $f2 = function () {
        };

        Monkey::filters()
              ->expectAdded('double_filter')
              ->once()
              ->ordered()
              ->with($f1, 10);

        Monkey::filters()
              ->expectAdded('double_filter')
              ->once()
              ->ordered()
              ->with($f2, 20);

        add_filter('double_filter', $f1, 10);
        add_filter('double_filter', $f2, 20);
    }

    public function testRemoveAction()
    {
        Filters::expectAdded('the_title')->once();

        add_filter('the_title', [$this, __FUNCTION__], 20);

        static::assertTrue(has_filter('the_title', [$this, __FUNCTION__], 20));

        remove_filter('the_title', [$this, __FUNCTION__], 20);

        static::assertFalse(has_filter('the_title', [$this, __FUNCTION__], 20));
    }
}

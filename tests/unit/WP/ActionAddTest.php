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
use Brain\Monkey\WP\Actions;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class ActionAddTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Monkey::tearDownWP();
        parent::tearDown();
    }

    public function testAddNull()
    {
        add_action('init', 'strtolower', 20, 2);
        // just want to see that when called properly nothing bad happen
        static::assertTrue(true);
    }

    public function testAddReturnsTrue()
    {
        static::assertTrue(add_action('init', 'strtolower', 20, 2));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadHook()
    {
        add_action(true, 'strtolower', 20, 2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadCallback()
    {
        add_action('init', 'meeeeeeeeee', 20, 2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadPriority()
    {
        add_action('init', 'strtolower', 'early', 2);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailsIfBadNumArgs()
    {
        add_action('init', 'strtolower', 30, 'two');
    }

    public function testAddAndHas()
    {
        add_action('init', 'strtolower', 30, 1);
        add_action('init', function () {
            return true;
        });
        add_action('init', [$this, __FUNCTION__], 20);

        static::assertTrue(has_action('init', 'strtolower', 30, 1));
        static::assertTrue(has_action('init', 'function()'));
        static::assertTrue(has_action('init', get_class($this).'->'.__FUNCTION__.'()', 20));

        static::assertTrue(has_action('init', 'strtolower', 30));
        static::assertTrue(has_action('init', 'function()', 10, 1));
        static::assertTrue(has_action('init', ' function () ', 10, 1));
        static::assertTrue(has_action('init', get_class($this).'->'.__FUNCTION__.'()', 20, 1));

        static::assertFalse(has_action('init', 'strtolower'));
        static::assertFalse(has_action('init', 'function()', 10, 3));
        static::assertFalse(has_action('init', get_class($this).'->'.__FUNCTION__.'()'));

        static::assertFalse(has_action('pre_get_posts', 'strtolower', 30, 1));
        static::assertFalse(has_action('foo', 'function()'));
        static::assertFalse(has_action('bar', get_class($this).'->'.__FUNCTION__.'()', 20));
    }

    public function testAddAndHasWithMethods()
    {
        add_action('init', 'strtolower', 30, 1);
        add_action('init', function () {
            return true;
        });
        add_action('init', [$this, __FUNCTION__], 20);

        static::assertTrue(Monkey::actions()->has('init', 'strtolower', 30, 1));
        static::assertTrue(Monkey::actions()->has('init', 'function()'));
        static::assertTrue(Monkey::actions()->has('init', get_class($this).'->'.__FUNCTION__.'()', 20));

        static::assertTrue(Monkey::actions()->has('init', 'strtolower', 30));
        static::assertTrue(Monkey::actions()->has('init', 'function()', 10, 1));
        static::assertTrue(Monkey::actions()->has('init', get_class($this).'->'.__FUNCTION__.'()', 20, 1));

        static::assertFalse(Monkey::actions()->has('init', 'strtolower'));
        static::assertFalse(Monkey::actions()->has('init', 'function()', 10, 3));
        static::assertFalse(Monkey::actions()->has('init', get_class($this).'->'.__FUNCTION__.'()'));

        static::assertFalse(Monkey::actions()->has('pre_get_posts', 'strtolower', 30, 1));
        static::assertFalse(Monkey::actions()->has('foo', 'function()'));
        static::assertFalse(Monkey::actions()->has('bar', get_class($this).'->'.__FUNCTION__.'()', 20));
    }

    public function testAddWithoutCallback()
    {
        static::assertFalse(Monkey::actions()->has('init'));
        add_action('init', [$this, __FUNCTION__], 20);
        static::assertTrue(Monkey::actions()->has('init'));
    }

    public function testExpectAdded()
    {
        Actions::expectAdded('init')->times(3)->with(
            Mockery::anyOf('strtolower', 'strtoupper', [$this, __FUNCTION__]),
            Mockery::type('int')
        );
        Actions::expectAdded('foo')->never();
        Actions::expectAdded('wp_footer')->once();

        add_action('init', 'strtolower', 30);
        add_action('init', 'strtoupper', 20);
        add_action('init', [$this, __FUNCTION__], 20);
        add_action('wp_footer', function () {
            return 'baz';
        });

        static::assertTrue(Monkey::actions()->has('init', 'strtolower', 30));
        static::assertTrue(Monkey::actions()->has('init', 'strtoupper', 20));
    }

    public function testAddedSameActionDifferentArguments()
    {
        $f1 = function () {
        };

        $f2 = function () {
        };

        Monkey::actions()
              ->expectAdded('double_action')
              ->once()
              ->ordered()
              ->with($f1);

        Monkey::actions()
              ->expectAdded('double_action')
              ->once()
              ->ordered()
              ->with($f2);

        add_action('double_action', $f1);
        add_action('double_action', $f2);
    }

    public function testRemoveAction()
    {
        Actions::expectAdded('init')->once();

        add_action('init', [$this, __FUNCTION__], 20);

        static::assertTrue(has_action('init', [$this, __FUNCTION__], 20));

        remove_action('init', [$this, __FUNCTION__], 20);

        static::assertFalse(has_action('init', [$this, __FUNCTION__], 20));
    }
}

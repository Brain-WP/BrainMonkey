<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Api;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Mockery\Exception\InvalidCountException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class AddActionTest extends Monkey\Tests\TestCase
{
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

    public function testAddAndHas()
    {
        add_action('init', 'strtolower', 30, 1);
        add_action('init', function ( $x, ...$y ) { return true; });
        add_action('init', [new \ArrayObject(), 'getArrayCopy'], 5);

        static::assertTrue(has_action('init', 'strtolower'));
        static::assertTrue(has_action('init', 'function( $x, ...$y )'));
        static::assertTrue(has_action('init', 'ArrayObject->getArrayCopy()'));

        static::assertFalse(has_action('pre_get_posts', 'strtolower'));
        static::assertFalse(has_action('foo', 'function()'));
        static::assertFalse(has_action('baz', 'ArrayObject->getArrayCopy()'));
    }

    public function testAddAndHasWithoutCallback()
    {
        static::assertFalse(has_action('init'));
        add_action('init', [$this, __FUNCTION__], 20);
        static::assertTrue(has_action('init'));
    }

    public function testExpectAdded()
    {
        Actions\expectAdded('init')->times(3)->with(
            \Mockery::anyOf('strtolower', 'strtoupper', [$this, __FUNCTION__]),
            \Mockery::type('int')
        );
        Actions\expectAdded('foo')->never();
        Actions\expectAdded('wp_footer')->once();

        add_action('init', 'strtolower', 30);
        add_action('init', 'strtoupper', 20);
        add_action('init', [$this, __FUNCTION__], 20);
        add_action('wp_footer', function () {
            return 'baz';
        });

        static::assertTrue(has_action('init', 'strtolower'));
        static::assertTrue(has_action('init', 'strtoupper'));
    }

    public function testAddedSameActionDifferentArguments()
    {
        Actions\expectAdded('double_action')
              ->once()
              ->ordered()
              ->with('a_function_name');

        Actions\expectAdded('double_action')
              ->once()
              ->ordered()
              ->with('another_function_name');

        add_action('double_action', 'a_function_name');
        add_action('double_action', 'another_function_name');
    }

    public function testRemoveAction()
    {
        Actions\expectAdded('init')->once();

        add_action('init', [$this, __FUNCTION__], 20);

        static::assertTrue(has_action('init', [$this, __FUNCTION__]));

        remove_action('init', [$this, __FUNCTION__], 20);

        static::assertFalse(has_action('init', [$this, __FUNCTION__]));
    }

    public function testAddActionWhenHappen()
    {
        Actions\expectAdded('foo')->once()->whenHappen(function($callable, $priority, $args) {
            $callable();
            static::assertSame(20, $priority);
            static::assertSame(2, $args);
        });

        $this->expectOutputString('Foo!');

        add_action( 'foo', function() {
            echo 'Foo!';
        }, 20, 2);
    }

    public function testAndAlsoExpect()
    {
        Actions\expectAdded('foo')
            ->once()
            ->ordered()
            ->with('__return_true', 10)
            ->andAlsoExpectIt()
            ->once()
            ->ordered()
            ->with('__return_false', 20);

        add_action('foo', '__return_true', 10);
        add_action('foo', '__return_false', 20);
    }

    public function testExpectWithNoArgsFailsIfNotAdded()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Actions\expectAdded('init');
    }
}

<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Functional;

use Brain\Monkey;
use Brain\Monkey\Tests\FunctionalTestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class FiltersTest extends FunctionalTestCase
{
    public function testExpectAdded()
    {
        Monkey\Filters\expectAdded('my_filter')->once();

        add_filter(
            'my_filter',
            function ($thing) {
                return $thing;
            },
            1,
            2
        );
    }

    public function testExpectApplied()
    {
        Monkey\Filters\expectApplied('the_title')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturnUsing(
                function ($title) {
                    return strtoupper($title);
                }
            );

        $title = apply_filters('the_title', 'Hello World');

        static::assertSame('HELLO WORLD', $title);
    }

    public function testHas()
    {
        add_filter(
            'the_title',
            [$this, __FUNCTION__],
            4
        );

        static::assertNotFalse(Monkey\Filters\has('the_title', [$this, __FUNCTION__]));

        static::assertTrue(Monkey\Filters\has('the_title', [$this, __FUNCTION__], 4));
        static::assertFalse(Monkey\Filters\has('the_title', [$this, __FUNCTION__], 0));
        static::assertFalse(Monkey\Filters\has('the_title', [$this, __FUNCTION__], 99));
    }

    public function testRemove()
    {
        Monkey\Filters\expectRemoved('my_hook')
            ->once()
            ->with('my_callback', 10);

        add_filter('my_hook', 'my_callback');

        $removed = remove_filter('my_hook', 'my_callback');

        static::assertTrue($removed);
    }


    public function testExpectAppliedThenDone()
    {
        /** @var callable|null $on_my_hook */
        $on_my_hook = null;

        Monkey\Filters\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), 1, 2)
            ->once()
            ->whenHappen(
                static function (callable $callback) use (&$on_my_hook) {
                    $on_my_hook = $callback;
                }
            );

        Monkey\Filters\expectApplied('my_hook')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('string'))
            ->andReturnUsing(
                static function ($a, $b) use (&$on_my_hook) {
                    return $on_my_hook($a, $b);
                }
            );

        add_filter(
            'my_hook',
            function ($a, $b) {
                return strtoupper("{$a} {$b}");
            },
            1,
            2
        );

        $hello = apply_filters('my_hook', 'Hello', 'World');

        static::assertSame('HELLO WORLD', $hello);
    }

    public function testExpectAppliedThenDoneDeprecated()
    {
        /** @var callable|null $on_my_hook */
        $on_my_hook = null;

        Monkey\Filters\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), 1, 2)
            ->once()
            ->whenHappen(
                static function (callable $callback) use (&$on_my_hook) {
                    $on_my_hook = $callback;
                }
            );

        Monkey\Filters\expectApplied('my_hook')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('string'))
            ->andReturnUsing(
                static function ($a, $b) use (&$on_my_hook) {
                    return $on_my_hook($a, $b);
                }
            );

        add_filter(
            'my_hook',
            function ($a, $b) {
                return strtoupper("{$a} {$b}");
            },
            1,
            2
        );

        $hello = apply_filters_deprecated('my_hook', array('Hello', 'World'), 'x.x.x', 'Replacement');

        static::assertSame('HELLO WORLD', $hello);
    }
}

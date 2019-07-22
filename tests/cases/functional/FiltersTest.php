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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class FiltersTest extends Monkey\Tests\FunctionalTestCase
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
}

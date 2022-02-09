<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Functional;

use Brain\Monkey;
use Brain\Monkey\Tests\FunctionalTestCase;

/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package Brain\Monkey\Tests
 */
class FiltersTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function testExpectAdded()
    {
        Monkey\Filters\expectAdded('my_filter')->once();

        add_filter(
            'my_filter',
            static function ($thing) {
                return $thing;
            },
            1,
            2
        );
    }

    /**
     * @test
     */
    public function testExpectApplied()
    {
        Monkey\Filters\expectApplied('the_title')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturnUsing(
                static function ($title) {
                    return strtoupper($title);
                }
            );

        $title = apply_filters('the_title', 'Hello World');

        static::assertSame('HELLO WORLD', $title);
    }

    /**
     * @test
     */
    public function testRemove()
    {
        Monkey\Filters\expectRemoved('my_hook')
            ->once()
            ->with('my_callback', 10);

        add_filter('my_hook', 'my_callback');

        $removed = remove_filter('my_hook', 'my_callback');

        static::assertTrue($removed);
    }

    /**
     * @test
     */
    public function testExpectAppliedThenDone()
    {
        /** @var callable|null $onMyHook */
        $onMyHook = null;

        Monkey\Filters\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), 1, 2)
            ->once()
            ->whenHappen(
                static function (callable $callback) use (&$onMyHook) {
                    $onMyHook = $callback;
                }
            );

        Monkey\Filters\expectApplied('my_hook')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('string'))
            ->andReturnUsing(
                static function ($left, $right) use (&$onMyHook) {
                    return $onMyHook($left, $right);
                }
            );

        add_filter(
            'my_hook',
            static function ($left, $right) {
                return strtoupper("{$left} {$right}");
            },
            1,
            2
        );

        $hello = apply_filters('my_hook', 'Hello', 'World');

        static::assertSame('HELLO WORLD', $hello);
    }

    /**
     * @test
     */
    public function testExpectAppliedThenDoneDeprecated()
    {
        /** @var callable|null $onMyHook */
        $onMyHook = null;

        Monkey\Filters\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), 1, 2)
            ->once()
            ->whenHappen(
                static function (callable $callback) use (&$onMyHook) {
                    $onMyHook = $callback;
                }
            );

        Monkey\Filters\expectApplied('my_hook')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('string'))
            ->andReturnUsing(
                static function ($left, $right) use (&$onMyHook) {
                    return $onMyHook($left, $right);
                }
            );

        add_filter(
            'my_hook',
            static function ($left, $right) {
                return strtoupper("{$left} {$right}");
            },
            1,
            2
        );

        $hello = apply_filters_deprecated('my_hook', ['Hello', 'World'], 'x.x.x', 'Replacement');

        static::assertSame('HELLO WORLD', $hello);
    }
}

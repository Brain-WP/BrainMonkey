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
class ActionsTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function testExpectAdded()
    {
        $this->expectOutputString('Hello');

        Monkey\Actions\expectAdded('init')
            ->with(\Mockery::type('callable'), 1, 2)
            ->whenHappen(
                static function (callable $callback) {
                    $callback();
                }
            );

        add_action(
            'init',
            static function () {
                echo "Hello";
            },
            1,
            2
        );
    }

    /**
     * @test
     */
    public function testExpectDone()
    {
        $this->expectOutputString('Hello World');

        Monkey\Actions\expectDone('my_hook')
            ->with(\Mockery::type('string'), \Mockery::type('string'))
            ->whenHappen(
                static function ($left, $right) {
                    echo "{$left} {$right}";
                }
            );

        do_action('my_hook', 'Hello', 'World');
    }

    /**
     * @test
     */
    public function testHas()
    {
        add_action(
            'init',
            function (\Foo\Bar ...$bar) {
                var_export($this);
            },
            1,
            2
        );

        static::assertNotFalse(
            Monkey\Actions\has(
                'init',
                function (\Foo\Bar ...$bar) {
                    var_export($this);
                },
                1,
                2
            )
        );
        static::assertNotFalse(Monkey\Actions\has('init', 'function (Foo\Bar ...$bar)', 1, 2));
        static::assertNotFalse(Monkey\Actions\has('init', 'function (Foo\\Bar ...$bar)', 1, 2));
    }

    /**
     * @test
     */
    public function testRemove()
    {
        Monkey\Actions\expectRemoved('my_hook')
            ->once()
            ->with('my_callback', 30);

        add_action('my_hook', 'my_callback', 30);

        $removed = remove_action('my_hook', 'my_callback', 30);

        static::assertTrue($removed);
    }

    /**
     * @test
     */
    public function testExpectAppliedThenDone()
    {
        $this->expectOutputString('Hello World');

        /** @var callable|null $onMyHook */
        $onMyHook = null;

        Monkey\Actions\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), \Mockery::type('int'), 2)
            ->whenHappen(
                static function (callable $callback) use (&$onMyHook) {
                    $onMyHook = $callback;
                }
            );

        Monkey\Actions\expectDone('my_hook')
            ->whenHappen(
                static function (...$args) use (&$onMyHook) {
                    $onMyHook(...$args);
                }
            );

        add_action(
            'my_hook',
            static function ($left, $right) {
                echo "{$left} {$right}";
            },
            1,
            2
        );

        do_action('my_hook', 'Hello', 'World');
    }

    /**
     * @test
     */
    public function testExpectAppliedThenDoneDeprecated()
    {
        $this->expectOutputString('Hello World');

        /** @var callable|null $onMyHook */
        $onMyHook = null;

        Monkey\Actions\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), \Mockery::type('int'), 2)
            ->whenHappen(
                static function (callable $callback) use (&$onMyHook) {
                    $onMyHook = $callback;
                }
            );

        Monkey\Actions\expectDone('my_hook')
            ->whenHappen(
                static function (...$args) use (&$onMyHook) {
                    $onMyHook(...$args);
                }
            );

        add_action(
            'my_hook',
            static function ($left, $right) {
                echo "{$left} {$right}";
            },
            1,
            2
        );

        do_action_deprecated('my_hook', ['Hello', 'World'], 'x.x.x.', 'Replacement');
    }
}

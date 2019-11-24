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
class ActionsTest extends FunctionalTestCase
{
    public function testExpectAdded()
    {
        $this->expectOutputString('Hello');

        Monkey\Actions\expectAdded('init')
            ->with(\Mockery::type('callable'), 1, 2)
            ->whenHappen(
                function (callable $callback) {
                    $callback();
                }
            );

        add_action(
            'init',
            function () {
                echo "Hello";
            },
            1,
            2
        );
    }

    public function testExpectDone()
    {
        $this->expectOutputString('Hello World');

        Monkey\Actions\expectDone('my_hook')
            ->with(\Mockery::type('string'), \Mockery::type('string'))
            ->whenHappen(
                function ($a, $b) {
                    echo "{$a} {$b}";
                }
            );

        do_action('my_hook', 'Hello', 'World');
    }

    public function testRemove()
    {
        Monkey\Actions\expectRemoved('my_hook')
            ->once()
            ->with('my_callback', 30);

        add_action('my_hook', 'my_callback', 30);

        $removed = remove_action('my_hook', 'my_callback', 30);

        static::assertTrue($removed);
    }

    public function testExpectAppliedThenDone()
    {
        $this->expectOutputString('Hello World');

        /** @var callable|null $on_my_hook */
        $on_my_hook = null;

        Monkey\Actions\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), \Mockery::type('int'), 2)
            ->whenHappen(
                static function (callable $callback) use (&$on_my_hook) {
                    $on_my_hook = $callback;
                }
            );

        Monkey\Actions\expectDone('my_hook')
            ->whenHappen(
                static function (...$args) use (&$on_my_hook) {
                    $on_my_hook(...$args);
                }
            );

        add_action(
            'my_hook',
            function ($a, $b) {
                echo "{$a} {$b}";
            },
            1,
            2
        );

        do_action('my_hook', 'Hello', 'World');
    }

    public function testExpectAppliedThenDoneDeprecated()
    {
        $this->expectOutputString('Hello World');

        /** @var callable|null $on_my_hook */
        $on_my_hook = null;

        Monkey\Actions\expectAdded('my_hook')
            ->with(\Mockery::type('callable'), \Mockery::type('int'), 2)
            ->whenHappen(
                static function (callable $callback) use (&$on_my_hook) {
                    $on_my_hook = $callback;
                }
            );

        Monkey\Actions\expectDone('my_hook')
            ->whenHappen(
                static function (...$args) use (&$on_my_hook) {
                    $on_my_hook(...$args);
                }
            );

        add_action(
            'my_hook',
            function ($a, $b) {
                echo "{$a} {$b}";
            },
            1,
            2
        );

        do_action_deprecated('my_hook', array('Hello', 'World'), 'x.x.x.', 'Replacement');
    }
}

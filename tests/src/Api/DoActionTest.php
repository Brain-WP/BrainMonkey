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
class DoActionTest extends Monkey\Tests\TestCase
{

    public function testDoNull()
    {
        do_action('init', 'foo', 'bar', 'baz');
        do_action_ref_array('init', ['foo', 'bar', 'baz']);

        // just want to see that when called properly nothing bad happen
        static::assertTrue(true);
    }

    public function testDoReturnsNull()
    {
        $nullDo = do_action('init', 'foo', 'bar', 'baz');
        $nullDoRef = do_action_ref_array('init', ['foo', 'bar', 'baz']);

        static::assertNull($nullDo);
        static::assertNull($nullDoRef);
    }

    public function testDoDid()
    {
        do_action('foo.bar');
        do_action('bar', 'baz');
        do_action('bar', ['foo', 'bar']);
        do_action_ref_array('by_ref', ['foo', 'bar', 'baz']);

        static::assertSame(1, did_action('foo.bar'));
        static::assertSame(2, did_action('bar'));
        static::assertSame(1, did_action('by_ref'));
        static::assertSame(0, did_action('not me'));
    }

    public function testDoWithExpectation()
    {
        Actions\expectDone('foo')->twice()->with(\Mockery::anyOf('Yes!', 'No!'));
        Actions\expectDone('bar')->once()->withAnyArgs();
        Actions\expectDone('not_me')->never();

        do_action('foo', 'Yes!');
        do_action_ref_array('foo', ['No!']);
        do_action('bar', 'foo', 'bar');
    }

    public function testDoWithExpectationWhenHappen()
    {
        $works = '';
        Actions\expectDone('foo')
            ->atLeast()
            ->once()
            ->whenHappen(function ($yes) use (&$works) {
                $works = $yes;
            });

        $sum = 0;
        Actions\expectDone('sum')
            ->times(3)
            ->with(\Mockery::type('int'))
            ->whenHappen(function ($n) use (&$sum) {
                $sum += $n;
            });

        do_action('foo', 'Yes!');
        do_action('sum', 1);
        do_action('sum', 3);
        do_action('sum', 6);

        static::assertSame('Yes!', $works);
        static::assertSame(10, $sum);
    }

    public function testDoWithExpectationWhenHappenCurrentFilter()
    {
        $callback = function () {
            if (current_filter() !== 'what_you_say') {
                throw new \RuntimeException('Giuseppe, your code sucks!');
            } else {
                echo 'Monkey is great!';
            }
        };

        $this->expectOutputString('Monkey is great!');

        Actions\expectDone('what_you_say')->once()->whenHappen($callback);

        do_action('what_you_say');

    }

    public function testNestedDoWithExpectationWhenHappenDoingAction()
    {

        Actions\expectDone('first_level')->once()->whenHappen(function () {
            do_action('second_level', 'Catch me!');
            do_action('second_level', 'Catch me!');
        });

        Actions\expectDone('second_level')->twice()->whenHappen(function ($arg) {

            static::assertSame('Catch me!', $arg);
            static::assertTrue(current_filter() === 'second_level');
            static::assertTrue(doing_action('first_level'));
            static::assertTrue(doing_action('second_level'));
            // Checking for output will ensure above assertions have ran.
            echo 'Monkey is great!';
        });

        $this->expectOutputString('Monkey is great!Monkey is great!');

        do_action('first_level');

        static::assertFalse(doing_action('first_level'));
        static::assertFalse(doing_action('second_level'));
    }

    public function testDoSameActionDifferentArguments()
    {
        Actions\expectDone('double_action')
            ->once()
            ->ordered()
            ->with('arg_1');
        Actions\expectDone('double_action')
            ->once()
            ->ordered()
            ->with('arg_2');

        do_action('double_action', 'arg_1');
        do_action('double_action', 'arg_2');
    }

    public function testAndAlsoExpectIt()
    {
        Actions\expectDone('double_action')
            ->once()
            ->ordered()
            ->with('arg_1')
            ->andAlsoExpectIt()
            ->once()
            ->ordered()
            ->with('arg_2');

        do_action('double_action', 'arg_1');
        do_action('double_action', 'arg_2');
    }

    public function testExpectWithNoArgsFailsIfNotDone()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Actions\expectDone('init');
    }
}

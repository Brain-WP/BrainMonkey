<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Api;

use Brain\Monkey\Filters;
use Brain\Monkey\Tests\UnitTestCase;
use Mockery\Exception\InvalidCountException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class ApplyFiltersTest extends UnitTestCase
{

    public function testApplyNull()
    {
        apply_filters('the_title', 'foo', 'bar', 'baz');
        apply_filters_ref_array('the_title', ['foo', 'bar', 'baz']);
        // just want to see that when called properly nothing bad happen
        static::assertTrue(true);
    }

    public function testApplyReturnsFirstArg()
    {
        $foo = apply_filters('the_title', 'foo', 'bar', 'baz');
        $fooRef = apply_filters_ref_array('the_title', ['foo', 'bar', 'baz']);
        static::assertSame('foo', $foo);
        static::assertSame('foo', $fooRef);
    }

    public function testApplyApplied()
    {
        apply_filters('foo.bar', 'foo');
        apply_filters('bar', 'baz');
        apply_filters('bar', ['foo', 'bar']);
        apply_filters_ref_array('by_ref', ['foo', 'bar', 'baz']);

        static::assertSame(1, Filters\applied('foo.bar'));
        static::assertSame(2, Filters\applied('bar'));
        static::assertSame(1, Filters\applied('by_ref'));
        static::assertSame(0, Filters\applied('not me'));
    }

    public function testApplyWithExpectation()
    {
        Filters\expectApplied('foo')->twice()->with(\Mockery::anyOf('Yes!', 'No!'));
        Filters\expectApplied('bar')->once()->withAnyArgs();
        Filters\expectApplied('not_me')->never();

        apply_filters('foo', 'Yes!');
        apply_filters_ref_array('foo', ['No!']);
        apply_filters('bar', 'foo', 'bar');
    }

    public function testApplyWithExpectationAndReturn()
    {
        Filters\expectApplied('the_title')
            ->with('foo')
            ->andReturn('Changed!');

        Filters\expectApplied('the_content')
            ->twice()
            ->with(\Mockery::anyOf('one', 'two'))
            ->andReturn('Foo!', 'Bar!');

        Filters\expectApplied('the_excerpt')
            ->andReturnUsing(function ($var) {
                return str_rot13($var);
            });

        $title = apply_filters('the_title', 'foo');
        $contentOne = apply_filters('the_content', 'one');
        $contentTwo = apply_filters('the_content', 'two');
        $excerpt = apply_filters('the_excerpt', 'Zbaxrl vf terng!');

        static::assertSame('Changed!', $title);
        static::assertSame('Foo!', $contentOne);
        static::assertSame('Bar!', $contentTwo);
        static::assertSame('Monkey is great!', $excerpt);
    }

    public function testApplyWithExpectationAndReturnCurrentFilter()
    {
        $answer = function ($question) {

            if (current_filter() !== 'can_I_ask') {
                throw new \RuntimeException('Giuseppe, your code sucks!');
            }

            $answers = [
                'How is Monkey?' => 'Great!',
                'How is Milk?'   => 'Meh',
            ];

            return $answers[$question];
        };

        Filters\expectApplied('can_I_ask')->twice()->andReturnUsing($answer);

        static::assertSame('Great!', apply_filters('can_I_ask', 'How is Monkey?'));
        static::assertSame('Meh', apply_filters('can_I_ask', 'How is Milk?'));
    }

    public function testApplySameFilterDifferentArguments()
    {
        $obj = new \stdClass();

        Filters\expectApplied('double_filter')
            ->once()
            ->ordered()
            ->with('x', $obj, 'x');

        Filters\expectApplied('double_filter')
            ->once()
            ->ordered()
            ->with('x', $obj, 'y');

        Filters\expectApplied('double_filter')
            ->once()
            ->ordered()
            ->with('x', $obj, 'x');

        apply_filters('double_filter', 'x', $obj, 'x');
        apply_filters('double_filter', 'x', $obj, 'y');
        apply_filters('double_filter', 'x', $obj, 'x');
    }

    public function testApplySameFilterDifferentArgumentsWithoutCatchAll()
    {
        Filters\expectApplied('foo')->once()->with('No?')->andReturn('No!');
        Filters\expectApplied('foo')->once()->with('Yes?')->andReturn('Yes!');
        Filters\expectApplied('foo')->once()->with('Maybe?')->andReturn('Maybe!');

        $no = apply_filters('foo', 'No?');
        $yes = apply_filters('foo', 'Yes?');
        $maybe = apply_filters('foo', 'Maybe?');

        static::assertSame('No!', $no);
        static::assertSame('Yes!', $yes);
        static::assertSame('Maybe!', $maybe);
    }

    public function testApplySameFilterDifferentArgumentsWithCatchAll()
    {
        Filters\expectApplied('foo')->once()->with('No?')->andReturn('No!');
        Filters\expectApplied('foo')->once()->with('Yes?')->andReturn('Yes!');
        Filters\expectApplied('foo')->zeroOrMoreTimes()->withAnyArgs()->andReturnFirstArg();

        $no = apply_filters('foo', 'No?');
        $yes = apply_filters('foo', 'Yes?');
        $maybe = apply_filters('foo', 'Maybe?');

        static::assertSame('No!', $no);
        static::assertSame('Yes!', $yes);
        static::assertSame('Maybe?', $maybe);
    }

    public function testAddExpectationWithDifferentArgsDoesNotBreakApplyFilters()
    {
        Filters\expectApplied('foo')->once()->with(1);
        Filters\expectApplied('foo')->once()->with(2);

        $one = apply_filters('foo', 1);
        $two = apply_filters('foo', 2);

        static::assertSame(1, $one);
        static::assertSame(2, $two);
    }

    public function testAddExpectationWithSameArgsDoesNotBreakApplyFilters()
    {
        Filters\expectApplied('foo')->times(3);

        $one = apply_filters('foo', 1);
        $two = apply_filters('foo', 2);
        $three = apply_filters('foo', 3);

        static::assertSame(1, $one);
        static::assertSame(2, $two);
        static::assertSame(3, $three);
    }

    public function testExpectByDefaultReturnFirstArg()
    {
        Filters\expectApplied('the_title');

        $title = apply_filters('the_title', 'I am the title');

        static::assertSame('I am the title', $title);
    }

    public function testAndAlsoExpectIt()
    {
        $obj = new \stdClass();

        Filters\expectApplied('double_filter')
            ->once()
            ->ordered()
            ->with('x', $obj, 'x')
            ->andAlsoExpectIt()
            ->once()
            ->ordered()
            ->with('x', $obj, 'y')
            ->andAlsoExpectIt()
            ->once()
            ->ordered()
            ->with('x', $obj, 'x');

        apply_filters('double_filter', 'x', $obj, 'x');
        apply_filters('double_filter', 'x', $obj, 'y');
        apply_filters('double_filter', 'x', $obj, 'x');
    }

    public function testNestedFiltersAndDoingFilter()
    {

        Filters\expectApplied('first_level')->once()->andReturnUsing(function ($arg) {

            static::assertTrue(current_filter() === 'first_level');
            static::assertTrue(doing_filter(), 'doing_filter() without hook name doesn\'t work (in first level)');
            static::assertTrue(doing_filter(null), 'doing_filter() with null hook name doesn\'t work (in first level)');
            static::assertTrue(doing_filter('first_level'));
            static::assertFalse(doing_filter('second_level'));

            return apply_filters('second_level', $arg);
        });

        Filters\expectApplied('second_level')->once()->andReturnUsing(function ($arg) {

            static::assertSame('How is Monkey?', $arg);
            static::assertTrue(current_filter() === 'second_level');
            static::assertTrue(doing_filter(), 'doing_filter() without hook name doesn\'t work (in second level)');
            static::assertTrue(doing_filter('first_level'));
            static::assertTrue(doing_filter('second_level'));

            return 'Monkey is great!';
        });


        static::assertSame('Monkey is great!', apply_filters('first_level', 'How is Monkey?'));
        static::assertFalse(doing_filter(), 'doing_filter() without hook name doesn\'t work (in outer code)');
        static::assertFalse(doing_filter(null), 'doing_filter() with null hook name doesn\'t work (in outer code)');
        static::assertFalse(doing_filter('first_level'));
        static::assertFalse(doing_filter('second_level'));
    }

    public function testExpectWithNoArgsFailsIfNotApplied()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Filters\expectApplied('the_title');
    }
}

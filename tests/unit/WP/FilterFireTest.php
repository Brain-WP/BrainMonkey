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
class FilterFireTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Monkey::tearDownWP();
        parent::tearDown();
    }

    public function testApplyNull()
    {
        apply_filters('the_title', 'foo', 'bar', 'baz');
        apply_filters_ref_array('the_title', ['foo', 'bar', 'baz']);
        // just want to see that when called properly nothing bad happen
        assertTrue(true);
    }

    public function testApplyReturnsFirstArg()
    {
        $foo = apply_filters('the_title', 'foo', 'bar', 'baz');
        $fooRef = apply_filters_ref_array('the_title', ['foo', 'bar', 'baz']);
        assertSame('foo', $foo);
        assertSame('foo', $fooRef);
    }

    /**
     * @expectedException \LogicException
     */
    public function testApplyFailsIfNoHook()
    {
        apply_filters();
    }

    /**
     * @expectedException \LogicException
     */
    public function testApplyFailsIfBadHook()
    {
        apply_filters(['foo']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testApplyRefFailsIfBadArgs()
    {
        apply_filters_ref_array('foo', 'bar');
    }

    public function testApplyApplied()
    {
        apply_filters('foo');
        apply_filters('bar', 'baz');
        apply_filters('bar', ['foo', 'bar']);
        apply_filters_ref_array('by_ref', ['foo', 'bar', 'baz']);

        assertSame(1, Monkey::filters()->applied('foo'));
        assertSame(2, Monkey::filters()->applied('bar'));
        assertSame(1, Monkey::filters()->applied('by_ref'));
        assertSame(0, Monkey::filters()->applied('not me'));
    }

    public function testApplyWithExpectation()
    {
        Monkey::filters()->expectApplied('foo')->twice()->with(Mockery::anyOf('Yes!', 'No!'));
        Filters::expectApplied('bar')->once()->withAnyArgs();
        Filters::expectApplied('not_me')->never();

        apply_filters('foo', 'Yes!');
        apply_filters_ref_array('foo', ['No!']);
        apply_filters('bar', 'foo', 'bar');
    }

    public function testApplyWithExpectationAndReturn()
    {
        Monkey::filters()->expectApplied('the_title')
              ->with('foo')
              ->atLeast()
              ->once()
              ->andReturn('Changed!');

        Monkey::filters()->expectApplied('the_content')
              ->twice()
              ->with(Mockery::anyOf('one', 'two'))
              ->andReturn('Foo!', 'Bar!');

        Monkey::filters()->expectApplied('the_excerpt')
              ->zeroOrMoreTimes()
              ->andReturnUsing(function ($var) {
                  return str_rot13($var);
              });

        $title = apply_filters('the_title', 'foo');
        $contentOne = apply_filters('the_content', 'one');
        $contentTwo = apply_filters('the_content', 'two');
        $excerpt = apply_filters('the_excerpt', 'Zbaxrl vf terng!');

        assertSame('Changed!', $title);
        assertSame('Foo!', $contentOne);
        assertSame('Bar!', $contentTwo);
        assertSame('Monkey is great!', $excerpt);
    }

    public function testApplyWithExpectationAndReturnCurrentFilter()
    {
        $answer = function ($question) {
            if (current_filter() !== 'can_I_ask') {
                throw new \RuntimeException('Giuseppe, your code sucks!');
            }
            if (substr(strrev($question), 0, 1) !== '?') {
                return 'This is not a question.';
            }
            $answers = [
                'Monkey' => 'Great!',
                'Milk'   => 'Meh',
            ];
            $words = explode(' ', $question);

            return $answers[substr(end($words), 0, -1)];
        };

        Monkey::filters()->expectApplied('can_I_ask')->twice()->andReturnUsing($answer);

        assertSame('Great!', apply_filters('can_I_ask', 'How is Monkey?'));
        assertSame('Meh', apply_filters('can_I_ask', 'How is Milk?'));
    }

    public function testApplySameFilterDifferentArguments()
    {
        $obj = new \stdClass();

        Monkey::filters()
              ->expectApplied('double_filter')
              ->once()
              ->ordered()
              ->with('x', $obj, 'x');

        Monkey::filters()
              ->expectApplied('double_filter')
              ->once()
              ->ordered()
              ->with('x', $obj, 'y');

        Monkey::filters()
              ->expectApplied('double_filter')
              ->once()
              ->ordered()
              ->with('x', $obj, 'x');

        apply_filters('double_filter', 'x', $obj, 'x');
        apply_filters('double_filter', 'x', $obj, 'y');
        apply_filters('double_filter', 'x', $obj, 'x');
    }
}

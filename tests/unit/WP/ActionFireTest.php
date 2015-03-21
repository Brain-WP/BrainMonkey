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
class ActionFireTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Monkey::tearDown();
        parent::tearDown();
    }

    public function testDoNull()
    {
        do_action('init', 'foo', 'bar', 'baz');
        do_action_ref_array('init', ['foo', 'bar', 'baz']);
        // just want to see that when called properly nothing bad happen
        assertTrue(true);
    }

    public function testDoReturnsNull()
    {
        $nullDo = do_action('init', 'foo', 'bar', 'baz');
        $nullDoRef = do_action_ref_array('init', ['foo', 'bar', 'baz']);
        assertNull($nullDo);
        assertNull($nullDoRef);
    }

    /**
     * @expectedException \LogicException
     */
    public function testDoFailsIfNoHook()
    {
        do_action();
    }

    /**
     * @expectedException \LogicException
     */
    public function testDoFailsIfBadHook()
    {
        do_action(['foo']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testDoRefFailsIfBadArgs()
    {
        do_action_ref_array('foo', 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDidFailsIfBadHook()
    {
        did_action(['foo']);
    }

    public function testDoDid()
    {
        do_action('foo');
        do_action('bar', 'baz');
        do_action('baz', ['foo', 'bar']);
        do_action_ref_array('by_ref', ['foo', 'bar', 'baz']);

        assertTrue(did_action('foo'));
        assertTrue(did_action('bar'));
        assertTrue(did_action('baz'));
        assertTrue(did_action('by_ref'));
        assertFalse(did_action('not me'));
    }

    public function testDoDidWithMethods()
    {
        do_action('foo');
        do_action('bar', 'baz');
        do_action('baz', ['foo', 'bar']);
        do_action_ref_array('by_ref', ['foo', 'bar', 'baz']);

        assertTrue(Monkey::actions()->did('foo'));
        assertTrue(Monkey::actions()->did('bar'));
        assertTrue(Monkey::actions()->did('baz'));
        assertTrue(Monkey::actions()->did('by_ref'));
        assertFalse(Monkey::actions()->did('not me'));
    }

    public function testDoWithExpectation()
    {
        Monkey::actions()->expectFired('foo')->twice()->with(Mockery::anyOf('Yes!', 'No!'));
        Actions::expectFired('bar')->once()->withAnyArgs();
        Actions::expectFired('not_me')->never();

        do_action('foo', 'Yes!');
        do_action_ref_array('foo', ['No!']);
        do_action('bar', 'foo', 'bar');
    }

    public function testDoWithExpectationWhenHappen()
    {
        $works = '';
        Monkey::actions()->expectFired('foo')
              ->atLeast()
              ->once()
              ->whenHappen(function ($yes) use (&$works) {
                  $works = $yes;
              });
        $sum = 0;
        Monkey::actions()->expectFired('sum')
              ->times(3)
              ->with(Mockery::type('int'))
              ->whenHappen(function ($n) use (&$sum) {
                  $sum += $n;
              });

        do_action('foo', 'Yes!');
        do_action('sum', 1);
        do_action('sum', 3);
        do_action('sum', 6);

        assertSame('Yes!', $works);
        assertSame(10, $sum);
    }

    public function testDoWithExpectationWhenHappenCurrentFilter()
    {
        $response = '';
        $callback = function () use (&$response) {
            if (current_filter() !== 'what_you_say') {
                throw new \RuntimeException('Giuseppe, your code sucks!');
            }
            $response = 'Monkey is great!';
        };

        Monkey::actions()->expectFired('what_you_say')->once()->whenHappen($callback);
        do_action('what_you_say');
        assertSame('Monkey is great!', $response);
    }

    /**
     * @expectedException \LogicException
     */
    public function testDoWithExpectationFailIfTryToReturn()
    {
        // well... that unicorns exist is a valid logic exception
        Monkey::actions()->expectFired('foo')->zeroOrMoreTimes()->andReturn('Unicorns exist!');
    }
}

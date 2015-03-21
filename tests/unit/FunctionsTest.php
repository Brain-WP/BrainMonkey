<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests;

use Brain\Monkey;
use PHPUnit_Framework_TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class FunctionsTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Functions::tearDown();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFunctionsFailIfBadFunctionName()
    {
        Functions::when('i;do_not_exists')->justReturn('Cool!');
    }

    public function testJustReturn()
    {
        Functions::when('i_do_not_exists')->justReturn('Cool!');
        Functions::when('i_return_null')->justReturn();
        assertSame('Cool!', i_do_not_exists());
        assertNull(i_return_null());
    }

    public function testPassThrough()
    {
        Functions::when('want_the_first')->passthru();
        Functions::when('want_the_second')->passthru(1);
        Functions::when('want_the_third')->passthru(2);
        assertSame('foo', want_the_first('foo', 'meh', 'meh'));
        assertSame('foo', want_the_second('meh', 'foo', 'meh'));
        assertSame('foo', want_the_third('meh', 'meh', 'foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPassThroughFailIfBadArg()
    {
        Functions::when('i_fail')->passthru('miserably');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testPassThroughFailIfNotReceived()
    {
        Functions::when('i_fail')->passthru(5);
        i_fail('miserably');
    }

    public function testAlias()
    {
        Functions::when('zerozeroseven')->alias(function ($james, $bond) {
            return "My name is {$bond}, {$james} {$bond}.";
        });
        assertSame('My name is Bond, James Bond.', zerozeroseven('James', 'Bond'));
    }

    public function testExpectNumberAndReturn()
    {
        Functions::expect('tween')->twice()->andReturn('first', 'second');
        assertSame('first', tween());
        assertSame('second', tween());
    }

    public function testExpectComplete()
    {
        Functions::expect('wow')
                 ->once()
                 ->with(200, Mockery::anyOf(800, 300), Mockery::type('string'))
                 ->andReturnUsing(function ($a, $b, $c) {
                     return (($a + $b) * 2).$c;
                 });

        assertSame("1000 times cool!", wow(200, 300, ' times cool!'));
    }

    public function testNamespacedFunctions()
    {
        Functions::when('test\a')->justReturn('A!');
        Functions::when('test\a\b')->passthru();
        Functions::when('test\a\b\c')->alias('str_rot13');
        Functions::expect('test\a\b\c\d')->atMost()->twice()->with(true)->andReturn('D!');

        assertSame('A!', \test\a());
        assertSame('B!', \test\a\b('B!'));
        assertSame('C!', \test\a\b\c('P!'));
        assertSame('D!', call_user_func('test\a\b\c\d', true));
    }

    public function testMonkeyFunctionsProxy()
    {
        Monkey::functions()->when('a')->justReturn('A!');
        Monkey::functions()->when('b')->passthru();
        Monkey::functions()->when('c')->alias('str_rot13');
        Monkey::functions()->expect('d')->atMost()->twice()->with(true)->andReturn('D!');

        assertSame('A!', a());
        assertSame('B!', b('B!'));
        assertSame('C!', c('P!'));
        assertSame('D!', call_user_func('d', true));
    }
}

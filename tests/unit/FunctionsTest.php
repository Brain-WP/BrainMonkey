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
        Monkey::tearDown();
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
        Functions::when('want_the_first')->returnArg();
        Functions::when('want_the_second')->returnArg(2);
        Functions::when('want_the_third')->returnArg(3);
        assertSame('foo', want_the_first('foo', 'meh', 'meh'));
        assertSame('foo', want_the_second('meh', 'foo', 'meh'));
        assertSame('foo', want_the_third('meh', 'meh', 'foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPassThroughFailIfBadArg()
    {
        Functions::when('i_fail')->returnArg('miserably');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testPassThroughFailIfNotReceived()
    {
        Functions::when('i_fail')->returnArg(5);
        i_fail('miserably');
    }

    public function testAlias()
    {
        Functions::when('zerozeroseven')->alias(function ($james, $bond) {
            return "My name is {$bond}, {$james} {$bond}.";
        });
        assertSame('My name is Bond, James Bond.', zerozeroseven('James', 'Bond'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testExpectFailIfShouldReceive()
    {
        Functions::expect('foo')->shouldReceive('foo');
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
        Functions::when('test\\a')->justReturn('A!');
        Functions::when('test\\a\\b')->returnArg();
        Functions::when('test\\a\\b\\c')->alias('str_rot13');

        Monkey::functions()->when('a')->justReturn('A!');
        Monkey::functions()->when('b')->returnArg();
        Monkey::functions()->when('c')->alias('str_rot13');
        Monkey::functions()->expect('buk')->atMost()->twice()->with(true)->andReturn('D!');

        assertSame('A!', a());
        assertSame('B!', b('B!'));
        assertSame('C!', c('P!'));
        assertSame('D!', buk(true));
    }

    public function testSameFunctionDifferentArguments()
    {
        Functions::expect('issue5')
                 ->with(true)
                 ->once()
                 ->ordered()
                 ->andReturn('First!');

        Functions::expect('issue5')
                 ->with(false)
                 ->once()
                 ->ordered()
                 ->andReturn('Second!');

        assertSame('First!', issue5(true));
        assertSame('Second!', issue5(false));
    }

    public function testJustEcho()
    {
        Functions::when('i_do_not_exists')->justEcho('Cool!');
        $this->expectOutputString('Cool!');
        i_do_not_exists();
    }

    public function testJustEchoEmptyString()
    {
        Functions::when('i_do_not_exists')->justEcho();
        $this->expectOutputString('');
        i_do_not_exists();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /can't echo a var of type array/
     */
    public function testJustEchoNotScalar()
    {
        Functions::when('i_do_not_exists')->justEcho(['foo']);
    }

    public function testJustEchoToStringObject()
    {
        $toString = Mockery::mock();
        $toString->shouldReceive('__toString')->andReturn('Cool!');

        Functions::when('i_do_not_exists')->justEcho($toString);
        $this->expectOutputString('Cool!');
        i_do_not_exists($toString);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /can't echo a var of type object/
     */
    public function testJustEchoObject()
    {
        Functions::when('i_do_not_exists')->justEcho(new \stdClass());
        i_do_not_exists();
    }

    public function testEchoArg()
    {
        Functions::when('i_do_not_exists')->echoArg(2);
        $this->expectOutputString('Cool!');
        i_do_not_exists(1, 'Cool!');
    }

    public function testEchoArgFirst()
    {
        Functions::when('i_do_not_exists')->echoArg();
        $this->expectOutputString('Cool!');
        i_do_not_exists('Cool!');
    }

    public function testEchoArgScalar()
    {
        Functions::when('i_do_not_exists')->echoArg();
        $this->expectOutputString('1');
        i_do_not_exists(1);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /can't echo it/
     */
    public function testEchoArgNotScalar()
    {
        Functions::when('i_do_not_exists')->echoArg(1);
        i_do_not_exists(['foo']);
    }

    public function testEchoArgToStringObject()
    {
        $toString = Mockery::mock();
        $toString->shouldReceive('__toString')->andReturn('Cool!');

        Functions::when('i_do_not_exists')->echoArg(1);
        $this->expectOutputString('Cool!');
        i_do_not_exists($toString);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /can't echo it/
     */
    public function testEchoArgObject()
    {
        Functions::when('i_do_not_exists')->echoArg(1);
        i_do_not_exists(new \stdClass());
    }
}

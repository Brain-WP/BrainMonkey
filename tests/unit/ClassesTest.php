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
use Brain\Monkey\Classes;
use Mockery;

/**
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class ClassesTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Monkey::tearDown();
    }

    /**
     * @dataProvider invalidMethodNamesProvider
     * @expectedException \InvalidArgumentException
     */
    public function testClassesFailIfInvalidMethodName($fullyQualifiedMethodName)
    {
        Classes::when($fullyQualifiedMethodName)->justReturn('Cool!');
    }

    public function invalidMethodNamesProvider(){
        return array(
          'semicolon not colon' => array( 'MyClass:;invalid' ),
          'too many colons' => array( 'MyClass:::invalid' ),
          'contains space' => array( 'My Class::invalid' ),
          'class starts with number' => array( '123MyClass::invalid' ),
          'method starts with number' => array( 'MyClass::123invalid' ),
        );
    }

    public function testClassesWithParanthesis()
    {
        Classes::when('MyClass::returnSomething()')->justReturn('Cool!');
    }

    public function testClassesFunction()
    {
        Monkey::classes()->when('MyClass::returnSomething')->justReturn('A!');
        Monkey::classes()->when('MyClass::returnSomethingElse')->returnArg();
        Monkey::classes()->when('MyClass::aliasMe')->alias('str_rot13');
        Monkey::classes()->expect('MyClass::doSomething')->atMost()->twice()->with(true)->andReturn('D!');
        Monkey::classes()->expect('MyClass::doSomething')->atMost()->twice()->with(false)->andReturn('E!');

        $myClass = new \MyClass();
        assertSame('A!',$myClass->returnSomething());
        assertSame('B!',$myClass->returnSomethingElse('B!'));
        assertSame('C!',$myClass->aliasMe('P!'));
        assertSame('D!',$myClass->doSomething(true));
        assertSame('D!',$myClass->doSomething(true));
        assertSame('E!',$myClass->doSomething(false));
    }

    public function testJustReturn()
    {

        Classes::when('MyClass::returnSomething()')->justReturn('Cool!');
        Classes::when('MyClass::returnNull()')->justReturn();
        $myClass = new \MyClass();
        assertSame('Cool!',$myClass->returnSomething());
        assertNull($myClass->returnNull());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPassThroughFailIfBadArg()
    {
        Classes::when('MyClass::returnSomething')->returnArg('miserably');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testPassThroughFailIfNotReceived()
    {

        Classes::when('MyClass::returnSomething')->returnArg(5);
        $myClass = new \MyClass();
        $myClass->returnSomething();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     */
    public function testAlias()
    {

        Classes::when('Villian::greetSpy')->alias(function ($title, $name) {
            return "{$title} {$name}, I've been expecting you.";
        });
        $blofeld  = new \Villian();
        assertSame('Mr Bond, I\'ve been expecting you.', $blofeld->greetSpy('Mr', 'Bond'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testExpectFailIfShouldReceive()
    {
        Classes::expect('MyClass::returnSomething')->shouldReceive('foo');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     */
    public function testExpectWith()
    {

        Classes::expect('MyClass::returnSomething')->with('test');

        $myClass = new \MyClass();
        $myClass->returnSomething( 'test' );
    }


    public function testExpectAndReturn()
    {
        Classes::expect('MyClass::returnSomething')->andReturn('I was called');
        $myClass = new \MyClass();
        assertSame('I was called', $myClass->returnSomething());
    }

    public function testExpectNumberAndReturn()
    {
        Classes::expect('MyClass::returnSomething')->twice()->andReturn('first', 'second');
        $myClass = new \MyClass();
        assertSame('first', $myClass->returnSomething());
        assertSame('second', $myClass->returnSomething());
    }

    public function testExpectComplete()
    {
        Classes::expect('MyClass::returnSomething')
            ->once()
            ->with(200, Mockery::anyOf(800, 300), Mockery::type('string'))
            ->andReturnUsing(function ($a, $b, $c) {
                return (($a + $b) * 2).$c;
            });

        $myClass = new \MyClass();
        assertSame("1000 times cool!", $myClass->returnSomething(200, 300, ' times cool!'));
    }


    public function testNamespacedClasses()
    {

        Classes::when('foo\\bar\\MyClass::returnSomething')->justReturn('namespaced');
        Classes::when('MyClass::returnSomething')->justReturn('not namespaced');

        $myGlobalClass = new \MyClass();
        $myNameSpacedClass = new \foo\bar\MyClass();

        assertSame('not namespaced', $myGlobalClass->returnSomething());
        assertSame('namespaced', $myNameSpacedClass->returnSomething());
    }

    public function testSameFunctionDifferentArguments()
    {
        Classes::expect('MyClass::returnSomething')
            ->with(true)
            ->once()
            ->ordered()
            ->andReturn('First!');

        Classes::expect('MyClass::returnSomething')
            ->with(false)
            ->once()
            ->ordered()
            ->andReturn('Second!');

        $myClass = new \MyClass();
        assertSame('First!', $myClass->returnSomething(true));
        assertSame('Second!', $myClass->returnSomething(false));
    }

    public function testJustEcho()
    {

        Classes::when('MyClass::echoSomething')->justEcho('Cool!');
        $this->expectOutputString('Cool!');
        $myClass = new \MyClass();
        $myClass->echoSomething();
    }

    public function testJustEchoEmptyString()
    {

        Classes::when('MyClass::echoSomething')->justEcho();
        $this->expectOutputString('');
        $myClass = new \MyClass();
        $myClass->echoSomething();
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /can't echo a var of type array/
     */
    public function testJustEchoNotScalar()
    {

        Classes::when('MyClass::echoSomething')->justEcho(['foo']);
    }

    public function testJustEchoToStringObject()
    {

        $toString = Mockery::mock();
        $toString->shouldReceive('__toString')->andReturn('Cool!');

        Classes::when('MyClass::echoSomething')->justEcho($toString);
        $this->expectOutputString('Cool!');
        $myClass = new \MyClass();
        $myClass->echoSomething($toString);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /can't echo a var of type object/
     */
    public function testJustEchoObject()
    {

        Classes::when('MyClass::echoSomething')->justEcho(new \stdClass());
        $myClass = new \MyClass();
        $myClass->echoSomething();
    }

    public function testEchoArg()
    {

        Classes::when('MyClass::echoSomething')->echoArg(2);
        $this->expectOutputString('Cool!');
        $myClass = new \MyClass();
        $myClass->echoSomething(1, 'Cool!');
    }

    public function testEchoArgFirst()
    {

        Classes::when('MyClass::echoSomething')->echoArg();
        $this->expectOutputString('Cool!');
        $myClass = new \MyClass();
        $myClass->echoSomething('Cool!');
    }

    public function testEchoArgScalar()
    {

        Classes::when('MyClass::echoSomething')->echoArg();
        $this->expectOutputString('1');
        $myClass = new \MyClass();
        $myClass->echoSomething(1);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /can't echo it/
     */
    public function testEchoArgNotScalar()
    {

        Classes::when('MyClass::echoSomething')->echoArg(1);
        $myClass = new \MyClass();
        $myClass->echoSomething(['foo']);
    }

    public function testEchoArgToStringObject()
    {

        $toString = Mockery::mock();
        $toString->shouldReceive('__toString')->andReturn('Cool!');

        Classes::when('MyClass::echoSomething')->echoArg(1);
        $this->expectOutputString('Cool!');
        $myClass = new \MyClass();
        $myClass->echoSomething($toString);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /can't echo it/
     */
    public function testEchoArgObject()
    {

        Classes::when('MyClass::echoSomething')->echoArg(1);
        $myClass = new \MyClass();
        $myClass->echoSomething(new \stdClass());
    }

}

<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Expectation;

use Brain\Monkey\Expectation\Exception\InvalidArgumentForStub;
use Brain\Monkey\Expectation\FunctionStub;
use Brain\Monkey\Name\FunctionName;
use Brain\Monkey\Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class FunctionStubTest extends TestCase
{

    public function testConstructorDeclareFunction()
    {
        new FunctionStub(new FunctionName('i_do_no_exists'));
        static::assertTrue(function_exists('i_do_no_exists'));
    }

    public function testAlias()
    {
        $rand = rand(1, 9999);
        (new FunctionStub(new FunctionName('i_do_no_exists')))->alias(function () use ($rand) {
            return $rand;
        });

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame($rand, i_do_no_exists());
    }

    public function name()
    {
        $root_fn = new FunctionStub(new FunctionName('i_do_no_exists'));

        $namespaced_fn = new FunctionStub(new FunctionName('foo\bar\i_do_no_exists'));

        static::assertSame('i_do_no_exists', $root_fn->name());
        static::assertSame('foo\bar\i_do_no_exists', $namespaced_fn->name());
    }

    public function testJustReturn()
    {
        $rand = rand(1, 9999);
        (new FunctionStub(new FunctionName('i_do_no_exists')))->justReturn($rand);

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame($rand, i_do_no_exists());
    }

    public function testJustEcho()
    {
        (new FunctionStub(new FunctionName('i_do_no_exists')))->justEcho('xyz');

        $this->expectOutputString('xyz');

        /** @noinspection PhpUndefinedFunctionInspection */
        i_do_no_exists();
    }

    public function testJustEchoThrowExceptionIfNotPrintable()
    {
        $stub = new FunctionStub(new FunctionName('i_do_no_exists'));

        $this->expectException(InvalidArgumentForStub::class);

        $stub->justEcho(new \stdClass());
    }

    public function testReturnArg()
    {
        (new FunctionStub(new FunctionName('i_do_no_exists')))->returnArg(2);

        $rand = rand(1, 9999);

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame($rand, i_do_no_exists(0, $rand));
    }

    public function testReturnArgThrowExceptionIfInvalidArg()
    {
        $stub = new FunctionStub(new FunctionName('i_do_no_exists'));

        $this->expectException(InvalidArgumentForStub::class);
        $stub->returnArg(3);

        /** @noinspection PhpUndefinedFunctionInspection */
        i_do_no_exists(1, 2);
    }

    public function testReturnArgThrowExceptionIfBadArg()
    {
        $stub = new FunctionStub(new FunctionName('i_do_no_exists'));

        $this->expectException(InvalidArgumentForStub::class);
        $stub->returnArg(0);

        /** @noinspection PhpUndefinedFunctionInspection */
        i_do_no_exists();
    }
}

<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Hook;

use Brain\Monkey\Hook\HookRunningStack;
use Brain\Monkey\Tests\UnitTestCase;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class HookRunningStackTest extends UnitTestCase
{

    public function testPushAndLast()
    {
        $running = new HookRunningStack();

        static::assertSame('', $running->last());
        static::assertSame($running, $running->push('foo'));
        static::assertSame('foo', $running->last());
        static::assertSame($running, $running->push('bar'));
        static::assertSame('bar', $running->last());
    }

    public function testPushAndHas()
    {
        $running = new HookRunningStack();

        static::assertFalse($running->has(''));
        static::assertFalse($running->has('foo'));
        static::assertSame($running, $running->push('foo'));
        static::assertFalse($running->has(''));
        static::assertTrue($running->has());
        static::assertTrue($running->has('foo'));
        static::assertFalse($running->has('bar'));
        static::assertSame($running, $running->push('bar'));
        static::assertFalse($running->has(''));
        static::assertTrue($running->has());
        static::assertTrue($running->has('foo'));
        static::assertTrue($running->has('bar'));
    }

    public function testReset()
    {
        $running = new HookRunningStack();
        static::assertSame($running, $running->push('foo'));
        static::assertSame($running, $running->push('bar'));
        static::assertTrue($running->has());
        static::assertTrue($running->has('foo'));
        static::assertTrue($running->has('bar'));
        static::assertSame($running, $running->reset());
        static::assertFalse($running->has());
        static::assertFalse($running->has('foo'));
        static::assertFalse($running->has('bar'));
    }

}
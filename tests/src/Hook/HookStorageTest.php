<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Hook;

use Brain\Monkey\Hook\Exception\InvalidHookArgument;
use Brain\Monkey\Hook\HookStorage;
use Brain\Monkey\Tests\TestCase;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class HookStorageTest extends TestCase
{

    public function testPushToAddedThrowsIfBadType()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded('meh', 'init', ['strtolower', 10, 1]);
    }

    public function testPushToAddedThrowsIfBadHook()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 100, ['strtolower', 10, 1]);
    }

    public function testPushToAddedThrowsIfBadCallback()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', ['', 10, 1]);
    }

    public function testPushToAddedThrowsIfBadPriority()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', ['strtolower', 'x', 1]);
    }

    public function testPushToAddedThrowsIfBadAcceptedArgs()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', ['strtolower', 1, 'meh']);
    }

    public function testPushToAddedThrowsIfEmptyArgs()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', []);
    }

    public function testPushToAddedAndIsHookAdded()
    {
        $storage = new HookStorage();

        $cb = function ($title) {
            return $title;
        };

        $storage->pushToAdded(HookStorage::FILTERS, 'the_title', [$cb, 10, 1]);

        $is_no_cb = $storage->isHookAdded(HookStorage::FILTERS, 'the_title');
        $is_with_cb = $storage->isHookAdded(HookStorage::FILTERS, 'the_title', $cb);
        $is_with_test = $storage->isHookAdded(HookStorage::FILTERS, 'the_title', 'test');

        $storage->removeFromAdded(HookStorage::FILTERS, 'the_title', [$cb, 10]);

        static::assertTrue($is_no_cb);
        static::assertTrue($is_with_cb);
        static::assertFalse($is_with_test);
        static::assertFalse($storage->isHookAdded(HookStorage::FILTERS, 'the_title', $cb));
        static::assertFalse($storage->isHookAdded(HookStorage::FILTERS, 'the_title'));
    }

    public function testPushToDoneFilterDoNotAllowEmptyArgs()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToDone(HookStorage::FILTERS, 'init', []);
    }

    public function testPushToDoneActionAllowEmptyArgs()
    {
        $storage = new HookStorage();
        $storage->pushToDone(HookStorage::ACTIONS, 'init', []);
        static::assertSame(1, $storage->isHookDone(HookStorage::ACTIONS, 'init'));
    }

    public function testInStorage()
    {
        $storage = new HookStorage();

        $storage->pushToAdded(HookStorage::ACTIONS, 'init', ['foo', 10, 1]);
        $storage->pushToAdded(HookStorage::FILTERS, 'the_title', ['strtolower', 10, 1]);

        $storage->pushToDone(HookStorage::ACTIONS, 'init', ['foo']);
        $storage->pushToDone(HookStorage::ACTIONS, 'init', []);
        $storage->pushToDone(HookStorage::FILTERS, 'the_title', ['Hello']);
        $storage->pushToDone(HookStorage::FILTERS, 'the_title', ['World']);

        static::assertTrue($storage->isHookAdded(HookStorage::ACTIONS, 'init'));
        static::assertTrue($storage->isHookAdded(HookStorage::ACTIONS, 'init', 'foo'));
        static::assertFalse($storage->isHookAdded(HookStorage::ACTIONS, 'init', 'foo\foo'));

        static::assertTrue($storage->isHookAdded(HookStorage::FILTERS, 'the_title'));
        static::assertTrue($storage->isHookAdded(HookStorage::FILTERS, 'the_title', 'strtolower'));
        static::assertFalse($storage->isHookAdded(HookStorage::FILTERS, 'the_title', 'foo'));

        self::assertSame(2, $storage->isHookDone(HookStorage::ACTIONS, 'init'));
        self::assertSame(0, $storage->isHookDone(HookStorage::ACTIONS, 'init_init'));
        self::assertSame(2, $storage->isHookDone(HookStorage::FILTERS, 'the_title'));
        self::assertSame(0, $storage->isHookDone(HookStorage::FILTERS, 'the_content'));
    }
}
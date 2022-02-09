<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Hook;

use Brain\Monkey\Hook\Exception\InvalidHookArgument;
use Brain\Monkey\Hook\HookStorage;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class HookStorageTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testPushToAddedThrowsIfBadType()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded('meh', 'init', ['strtolower', 10, 1]);
    }

    /**
     * @test
     */
    public function testPushToAddedThrowsIfBadHook()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 100, ['strtolower', 10, 1]);
    }

    /**
     * @test
     */
    public function testPushToAddedThrowsIfBadCallback()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', ['', 10, 1]);
    }

    /**
     * @test
     */
    public function testPushToAddedThrowsIfBadPriority()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', ['strtolower', 'x', 1]);
    }

    /**
     * @test
     */
    public function testPushToAddedThrowsIfBadAcceptedArgs()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', ['strtolower', 1, 'meh']);
    }

    /**
     * @test
     */
    public function testPushToAddedThrowsIfEmptyArgs()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', []);
    }

    /**
     * @test
     */
    public function testPushToAddedThrowsIfTooManyArgs()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToAdded(HookStorage::FILTERS, 'init', ['x', 1, 10, 11]);
    }

    /**
     * @test
     */
    public function testPushToAddedAndIsHookAdded()
    {
        $storage = new HookStorage();

        $function = static function ($title) {
            return $title;
        };

        $storage->pushToAdded(HookStorage::FILTERS, 'the_title', [$function, 10, 1]);

        $isWithoutFunction = $storage->isHookAdded(HookStorage::FILTERS, 'the_title');
        $isWithCb = $storage->isHookAdded(HookStorage::FILTERS, 'the_title', $function);
        $isWithTest = $storage->isHookAdded(HookStorage::FILTERS, 'the_title', 'test');

        $storage->removeFromAdded(HookStorage::FILTERS, 'the_title', [$function, 10]);

        static::assertTrue($isWithoutFunction);
        static::assertTrue($isWithCb);
        static::assertFalse($isWithTest);
        static::assertFalse($storage->isHookAdded(HookStorage::FILTERS, 'the_title', $function));
        static::assertFalse($storage->isHookAdded(HookStorage::FILTERS, 'the_title'));
    }

    /**
     * @test
     */
    public function testPushToDoneFilterDoNotAllowEmptyArgs()
    {
        $storage = new HookStorage();
        $this->expectException(InvalidHookArgument::class);
        $storage->pushToDone(HookStorage::FILTERS, 'init', []);
    }

    /**
     * @test
     */
    public function testPushToDoneActionAllowEmptyArgs()
    {
        $storage = new HookStorage();
        $storage->pushToDone(HookStorage::ACTIONS, 'init', []);
        static::assertSame(1, $storage->isHookDone(HookStorage::ACTIONS, 'init'));
    }

    /**
     * @test
     */
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

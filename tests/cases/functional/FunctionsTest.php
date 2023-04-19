<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Functional;

use Brain\Monkey;
use Brain\Monkey\Tests\FunctionalTestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class FunctionsTest extends FunctionalTestCase
{
    public function testWhen()
    {
        Monkey\Functions\when('get_post')->justReturn(false);

        static::assertFalse(get_post(1));
        static::assertFalse(get_post(2));
    }

    public function testExpect()
    {
        $post = \Mockery::mock(\WP_Post::class);

        Monkey\Functions\expect('get_post')
            ->once()
            ->with(1)
            ->andReturn(false)
            ->andAlsoExpectIt()
            ->twice()
            ->with(2)
            ->andReturn($post);

        static::assertFalse(get_post(1));
        static::assertSame($post, get_post(2));
        static::assertSame($post, get_post(2));
    }

    public function testPredefinedStubs()
    {
        $error = \Mockery::mock(\WP_Error::class);

        static::assertTrue(is_wp_error($error));
        static::assertFalse(is_wp_error('x'));

        static::assertTrue(wp_validate_boolean(true));
        static::assertFalse(wp_validate_boolean(false));
        static::assertFalse(wp_validate_boolean('false'));
        static::assertTrue(wp_validate_boolean(1));
        static::assertTrue(wp_validate_boolean('lorem ipsum'));

        static::assertSame('foo', wp_slash('foo'));
        static::assertSame(1, wp_slash(1));
        static::assertSame(['foo', 2, 'bar'], wp_slash(['foo', 2, 'bar']));
        static::assertSame('L\\\'x\\"y', wp_slash('L\'x"y'));
        static::assertSame(['L\\\'x\\"y', 'bar'], wp_slash(['L\'x"y', 'bar']));
    }

    public function testReDefinePredefinedStubsWithWhen()
    {
        Monkey\Functions\when('is_wp_error')->alias('ctype_alpha');

        static::assertTrue(is_wp_error('xyz'));
        static::assertFalse(is_wp_error('123'));
    }

    public function testReDefinePredefinedStubsWithExpect()
    {
        Monkey\Functions\expect('is_wp_error')
            ->atLeast()
            ->twice()
            ->andReturnUsing(
                function ($thing) {
                    return $thing !== 42;
                }
            );

        static::assertTrue(is_wp_error(123));
        static::assertFalse(is_wp_error(42));
        static::assertTrue(is_wp_error('xyz'));
    }
}

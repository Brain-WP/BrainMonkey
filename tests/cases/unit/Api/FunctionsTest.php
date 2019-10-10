<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Api;

use Brain\Monkey\Functions;
use Brain\Monkey\Tests\UnitTestCase;
use Mockery\Exception\InvalidCountException;
use PHPUnit\Framework\Error\Error as PHPUnit_Error;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class FunctionsTest extends UnitTestCase
{

    public function testJustReturn()
    {
        Functions\when('i_do_not_exists')->justReturn('Cool!');
        Functions\when('i_return_null')->justReturn();
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('Cool!', i_do_not_exists());
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertNull(i_return_null());
    }

    public function testReturnArg()
    {
        Functions\when('want_the_first')->returnArg();
        Functions\when('want_the_second')->returnArg(2);
        Functions\when('want_the_third')->returnArg(3);
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('foo', want_the_first('foo', 'meh', 'meh'));
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('foo', want_the_second('meh', 'foo', 'meh'));
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('foo', want_the_third('meh', 'meh', 'foo'));
    }

    public function testAlias()
    {
        Functions\when('zero_zero_seven')->alias(function ($james, $bond) {
            return "My name is {$bond}, {$james} {$bond}.";
        });

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('My name is Bond, James Bond.', zero_zero_seven('James', 'Bond'));
    }

    public function testExpectTimesAndReturn()
    {
        Functions\expect('tween')->twice()->andReturn('first', 'second');
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('first', tween());
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('second', tween());
    }

    public function testExpectComplete()
    {
        Functions\expect('wow')
            ->once()
            ->with(200, 300, \Mockery::type('string'))
            ->andReturnUsing(function ($a, $b, $c) {
                return (($a + $b) * 2).$c;
            });

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame("1000 times cool!", wow(200, 300, ' times cool!'));
    }

    public function testNamespacedFunctions()
    {
        Functions\when('test\\a')->justReturn('A!');
        Functions\when('test\\a\\b')->returnArg();
        Functions\when('test\\a\\b\\c')->alias('strtoupper');

        /** @noinspection PhpUndefinedFunctionInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        static::assertSame('A!', \test\a());
        /** @noinspection PhpUndefinedFunctionInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        static::assertSame('B!', \test\a\b('B!'));
        /** @noinspection PhpUndefinedFunctionInspection */
        /** @noinspection PhpUndefinedNamespaceInspection */
        static::assertSame('C!', \test\a\b\c('c!'));
    }

    public function testSameFunctionDifferentArguments()
    {
        Functions\expect('issue5')
            ->with(true)
            ->once()
            ->ordered()
            ->andReturn('First!');

        Functions\expect('issue5')
            ->with(false)
            ->once()
            ->ordered()
            ->andReturn('Second!');

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('First!', issue5(true));
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('Second!', issue5(false));
    }

    public function testJustEcho()
    {
        Functions\when('i_do_not_exists')->justEcho('Cool!');
        $this->expectOutputString('Cool!');
        /** @noinspection PhpUndefinedFunctionInspection */
        i_do_not_exists();
    }

    public function testJustEchoEmptyString()
    {
        Functions\when('i_do_not_exists')->justEcho();
        $this->expectOutputString('');
        /** @noinspection PhpUndefinedFunctionInspection */
        i_do_not_exists();
    }

    public function testEchoArg()
    {
        Functions\when('i_do_not_exists')->echoArg(2);
        $this->expectOutputString('Cool!');
        /** @noinspection PhpUndefinedFunctionInspection */
        i_do_not_exists(1, 'Cool!');
    }

    public function testEchoArgFirst()
    {
        Functions\when('i_do_not_exists')->echoArg();
        $this->expectOutputString('Cool!');
        /** @noinspection PhpUndefinedFunctionInspection */
        i_do_not_exists('Cool!');
    }

    public function testUndefinedFunctionTriggerErrorRightAfterDefinition()
    {
        $this->expectException(PHPUnit_Error::class);
        Functions\when('since_i_am_not_defined_i_will_trigger_error');
        $this->expectExceptionMessageRegExp('/since_i_am_not_defined_i_will_trigger_error.+not defined/');
        /** @noinspection PhpUndefinedFunctionInspection */
        since_i_am_not_defined_i_will_trigger_error();
    }

    /**
     * @depends testUndefinedFunctionTriggerErrorRightAfterDefinition
     */
    public function testUndefinedFunctionSurviveTests()
    {
        static::assertTrue(function_exists('since_i_am_not_defined_i_will_trigger_error'));
    }

    /**
     * @depends testUndefinedFunctionSurviveTests
     */
    public function testSurvivedFunctionStillTriggerError()
    {
        $this->expectException(PHPUnit_Error::class);
        $this->expectExceptionMessageRegExp('/since_i_am_not_defined_i_will_trigger_error.+not defined/');
        /** @noinspection PhpUndefinedFunctionInspection */
        since_i_am_not_defined_i_will_trigger_error();
    }

    /**
     * @depends testSurvivedFunctionStillTriggerError
     */
    public function testNothingJustMockASurvivedFunction()
    {
        Functions\when('since_i_am_not_defined_i_will_trigger_error')->justReturn(1234567890);
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame(1234567890, since_i_am_not_defined_i_will_trigger_error());
    }

    /**
     * @depends testNothingJustMockASurvivedFunction
     */
    public function testSurvivedFunctionStillTriggerErrorAfterBeingMocked()
    {
        $this->expectException(PHPUnit_Error::class);
        $this->expectExceptionMessageRegExp('/since_i_am_not_defined_i_will_trigger_error.+not defined/');
        /** @noinspection PhpUndefinedFunctionInspection */
        since_i_am_not_defined_i_will_trigger_error();
    }

    public function testAndAlsoExpectIt()
    {
        Functions\expect('also')
            ->with(1)
            ->once()
            ->ordered()
            ->andReturn('First!')
            ->andAlsoExpectIt()
            ->with(2)
            ->once()
            ->ordered()
            ->andReturn('Second!');

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('First!', also(1));
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertSame('Second!', also(2));
    }

    public function testExpectWithNoArgsFailsIfNotCalled()
    {
        $this->expectMockeryException(InvalidCountException::class);

        Functions\expect('test');
    }

    public function testStubsReturnValue()
    {
        Functions\stubs([
            'is_user_logged_in' => true,
            'current_user_can'  => true,
        ]);

        static::assertTrue(is_user_logged_in());
        static::assertTrue(current_user_can('x'));

        Functions\stubs([
            'is_user_logged_in' => 1,
            'current_user_can'  => 2,
        ]);

        static::assertSame(1, is_user_logged_in());
        static::assertSame(2, current_user_can('y'));
    }

    public function testStubsCallable()
    {
        Functions\stubs([
            'wp_get_current_user' => function () {
                $user = \Mockery::mock('\WP_User');
                $user->shouldReceive('to_array')->andReturn(['ID' => 123]);

                return $user;
            }
        ]);

        static::assertSame(['ID' => 123], wp_get_current_user()->to_array());
    }

    public function testStubsPassThrough()
    {
        Functions\stubs([
            'esc_attr',
            'esc_html',
            'esc_textarea',
        ]);

        static::assertSame('x', esc_attr('x'));
        static::assertSame('y', esc_html('y'));
        static::assertSame('z', esc_textarea('z'));
    }

    public function testStubsAll()
    {
        Functions\stubs([
            'is_user_logged_in'   => 'a',
            'current_user_can'    => 123,
            'wp_get_current_user' => function () {
                $user = \Mockery::mock('\WP_User');
                $user->shouldReceive('to_array')->andReturn(['ID' => 456]);

                return $user;
            },
            'esc_attr',
            'esc_html',
            'esc_textarea',
        ]);

        static::assertSame('a', is_user_logged_in());
        static::assertSame(123, current_user_can('xy'));
        static::assertSame(['ID' => 456], wp_get_current_user()->to_array());
        static::assertSame('!', esc_attr('!'));
        static::assertSame('?', esc_html('?'));
        static::assertSame('@', esc_textarea('@'));
    }

    public function testStubsEdgeCases()
    {
        Functions\stubs(
            [
                'i_return_null'       => function () {
                    return null;
                },
                'i_return_null_too'   => '__return_null',
                'i_return_a_callback' => function () {
                    return function() {
                        return 'yes';
                    };
                },
            ]
        );

        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertNull(i_return_null());
        /** @noinspection PhpUndefinedFunctionInspection */
        static::assertNull(i_return_null_too());
        /** @noinspection PhpUndefinedFunctionInspection */
        $cb = i_return_a_callback();
        static::assertSame('yes', $cb());
    }

    public function testStubsTranslationsReturn()
    {
        Functions\stubTranslationFunctions();

        static::assertSame('Foo', __('Foo', 'my-txt-domain'));
        static::assertSame('Foo!', _x('Foo!', 'context',  'my-txt-domain'));
        static::assertSame('Bar!', esc_html__('Bar!', 'my-txt-domain'));
        static::assertSame('Baz!', esc_attr__('Baz!', 'my-txt-domain'));
        static::assertSame('Foo bar', esc_attr_x('Foo bar', 'context',  'my-txt-domain'));
    }

    public function testStubsTranslationsEcho()
    {
        Functions\stubTranslationFunctions();

        $this->expectOutputString('ABCD');

        static::assertNull(_e('A', 'my-txt-domain'));
        static::assertNull(_ex('B', 'context',  'my-txt-domain'));
        static::assertNull(esc_html_e('C', 'my-txt-domain'));
        static::assertNull(esc_attr_e('D', 'my-txt-domain'));
    }

    public function testStubsEscapeFunctionsNoUrlNoSql()
    {
        Functions\stubEscapeFunctions();

        $lorem = '<b>Lorem ipsum</b>';
        $escaped = htmlspecialchars($lorem);

        static::assertSame($escaped, esc_html($lorem));
        static::assertSame($escaped, esc_attr($lorem));
        static::assertSame($escaped, esc_js($lorem));
        static::assertSame($escaped, esc_textarea($lorem));
    }

    public function testStubsEscapeUrl()
    {
        Functions\stubEscapeFunctions();

        static::assertSame('http://www.example.com', esc_url('http://www.example.com'));
        static::assertSame('https://www.example.com', esc_url('https://www.example.com'));
        static::assertSame('http://no-schema', esc_url('no-schema'));
        static::assertSame('http://www.example.com?f&b=x', esc_url_raw('www.example.com?f&b=x'));
        static::assertSame(
            'http://example.com?f&b=x&#039;y&#038;',
            esc_url('http://example.com?f&b=x\'y&amp;')
        );
    }

    public function testStubsEscapeSql()
    {
        Functions\stubEscapeFunctions();

        static::assertSame("hello, \\'world\\'", esc_sql("hello, 'world'"));
        static::assertSame('<b>hello world</b>', esc_sql('<b>hello world</b>'));
    }
}

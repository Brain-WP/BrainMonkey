<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests;

use Brain\Monkey;
use PHPUnit\Framework\Error\Error as PHPUnit_Error;
use PHPUnit\Framework\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class UnitTestCase extends TestCase
{
    private $expect_mockery_exception = null;

    /**
     * @before
     */
    protected function setUpFixtures()
    {
        $this->expect_mockery_exception = null;
        $libPath = explode('/tests/src/', str_replace('\\', '/', __FILE__))[0];

        require_once "{$libPath}/inc/wp-helper-functions.php";
        require_once "{$libPath}/inc/wp-hook-functions.php";
    }

    /**
     * @after
     */
    protected function tearDownFixtures()
    {
        if ( ! $this->expect_mockery_exception) {
            Monkey\tearDown();

            return;
        }

        $this->tearDownMakingSureExpectedExceptionIsThrown();
    }

    /**
     * PHPUnit cross-version support.
     *
     * @param string $needle
     * @param string $haystack
     * @param string $message
     * @return void
     */
    public static function assertStringContains($needle, $haystack, $message = '') {

        if (method_exists(TestCase::class, 'assertStringContainsString')) {
            // PHPUnit 7.5+.
            TestCase::assertStringContainsString($needle, $haystack, $message);

            return;
        }

        // PHPUnit < 7.5.
        static::assertContains($needle, $haystack, $message);
    }

    /**
     * PHPUnit cross-version support.
     */
    protected function expectErrorException()
    {
        if (method_exists($this, 'expectError')) {
            // PHPUnit 8.4+.
            $this->expectError();
            return;
        }

        // PHPUnit < 8.4.
        $this->expectException(PHPUnit_Error::class);
    }

    /**
     * PHPUnit cross-version support.
     *
     * @param string $msgRegex
     * @return void
     */
    protected function expectExceptionMsgRegex($msgRegex)
    {
        if (method_exists($this, 'expectExceptionMessageMatches')) {
            // PHPUnit 8.4+.
            $this->expectExceptionMessageMatches($msgRegex);
            return;
        }

        // PHPUnit < 8.4.
        $this->expectExceptionMessageRegExp($msgRegex);
    }

    /**
     * We can't use PHPUnit expectException() because we need to wait for `Monkey\tearDown` and that
     * does not work for `expectException()`.
     *
     * So we let tests use TestCase::expectMockeryException() to set the expectation on thrown
     * exception, and when that is thrown we do nothing, but we throw PHPUnit exception in case it
     * is not thrown and we expected it.
     *
     * @return void
     */
    protected function tearDownMakingSureExpectedExceptionIsThrown()
    {
        try {
            Monkey\tearDown();

            throw new \PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    'Failed asserting that Mockery exception %s is thrown.',
                    $this->expect_mockery_exception
                )
            );
        } catch (\Throwable $e) {
            if (get_class($e) !== $this->expect_mockery_exception) {
                throw $e;
            }
        } catch (\Exception $e) {
            if (get_class($e) !== $this->expect_mockery_exception) {
                throw $e;
            }
        }
    }

    /**
     * @param string $class The expected Mockery exception class name
     */
    protected function expectMockeryException($class)
    {
        if ( ! class_exists($class) || ! is_subclass_of($class, \Exception::class, true)) {
            throw new \PHPUnit_Framework_Exception(
                sprintf(
                    '%s is not a valid Mockery exception class name.',
                    $class
                )

            );
        }

        $this->expect_mockery_exception = $class;
    }
}

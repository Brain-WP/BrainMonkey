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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class UnitTestCase extends \PHPUnit_Framework_TestCase
{
    private $expect_mockery_exception = null;

    protected function setUp()
    {
        $this->expect_mockery_exception = null;
        $libPath = explode('/tests/src/', str_replace('\\', '/', __FILE__))[0];

        require_once "{$libPath}/inc/wp-helper-functions.php";
        require_once "{$libPath}/inc/wp-hook-functions.php";
    }

    protected function tearDown()
    {
        if ( ! $this->expect_mockery_exception) {
            Monkey\tearDown();

            return;
        }

        $this->tearDownMakingSureExpectedExceptionIsThrown();
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
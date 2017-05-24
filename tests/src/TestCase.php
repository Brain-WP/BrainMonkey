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
class TestCase extends \PHPUnit_Framework_TestCase
{

    private $expect_mockery_exception = null;

    protected function tearDown()
    {
        if ( ! $this->expect_mockery_exception) {
            Monkey\tearDown();

            return;
        }

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
        } finally {
            $this->expect_mockery_exception = null;
        }
    }

    /**
     * @param string $class The expected Mockery exception class name
     */
    protected function expectMockeryException($class)
    {
        if ( ! class_exists($class) || ! is_subclass_of($class, \Exception::class, true)) {
            throw new \PHPUnit_Framework_Exception(
                sprintf('%s is not a valid Mockery exception class name.'),
                $class
            );
        }

        $this->expect_mockery_exception = $class;
    }
}
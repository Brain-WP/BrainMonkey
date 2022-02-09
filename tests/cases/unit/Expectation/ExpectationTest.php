<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Brain\Monkey\Expectation\Exception\NotAllowedMethod;
use Brain\Monkey\Expectation\Expectation;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\UnitTestCase;
use Mockery\ExpectationInterface;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExpectationTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testNotAllowedMethodsThrowException()
    {
        /** @var \Mockery\ExpectationInterface $mockery */
        $mockery = \Mockery::mock(ExpectationInterface::class);

        $target = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, 'foo');

        $expectation = new Expectation($mockery, $target);

        $this->expectException(NotAllowedMethod::class);

        /** @noinspection PhpUndefinedMethodInspection */
        $expectation->shouldReceive('foo');
    }

    /**
     * @test
     */
    public function testReturnExpectationThrowExceptionIfNotAllowed()
    {
        /** @var \Mockery\ExpectationInterface $mockery */
        $mockery = \Mockery::mock(ExpectationInterface::class);

        $target = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, 'foo');

        $expectation = new Expectation($mockery, $target);

        $this->expectException(NotAllowedMethod::class);
        $this->expectExceptionCode(NotAllowedMethod::CODE_RETURNING_METHOD);

        /** @noinspection PhpUndefinedMethodInspection */
        $expectation->andReturn();
    }

    /**
     * @test
     */
    public function testWithNoArgsThrowExceptionIfArgsRequired()
    {
        /** @var \Mockery\ExpectationInterface $mockery */
        $mockery = \Mockery::mock(ExpectationInterface::class);

        $target = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');

        $expectation = new Expectation($mockery, $target);

        $this->expectException(ExpectationArgsRequired::class);

        $expectation->withNoArgs();
    }

    /**
     * @test
     */
    public function testWhenHappenExpectationThrowExceptionIfNotAllowed()
    {
        /** @var \Mockery\ExpectationInterface $mockery */
        $mockery = \Mockery::mock(ExpectationInterface::class);

        $target = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_APPLIED, 'foo');

        $expectation = new Expectation($mockery, $target);

        $this->expectException(NotAllowedMethod::class);
        $this->expectExceptionCode(NotAllowedMethod::CODE_WHEN_HAPPEN);

        $expectation->whenHappen('__return_true');
    }

    /**
     * @test
     */
    public function testAndReturnFirstArgThrowExceptionIfNotAllowed()
    {
        /** @var \Mockery\ExpectationInterface $mockery */
        $mockery = \Mockery::mock(ExpectationInterface::class);

        $target = new ExpectationTarget(ExpectationTarget::TYPE_FILTER_ADDED, 'foo');

        $expectation = new Expectation($mockery, $target);

        $this->expectException(NotAllowedMethod::class);

        $expectation->andReturnFirstArg();
    }
}

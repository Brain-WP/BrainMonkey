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

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Brain\Monkey\Expectation\Exception\NotAllowedMethod;
use Brain\Monkey\Expectation\Expectation;
use Brain\Monkey\Expectation\ExpectationTarget;
use Brain\Monkey\Tests\TestCase;
use Mockery\ExpectationInterface;

class ExpectationTest extends TestCase
{

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

    public function testWithNoArgsThrowExceptionIfArgsRequired()
    {
        /** @var \Mockery\ExpectationInterface $mockery */
        $mockery = \Mockery::mock(ExpectationInterface::class);

        $target = new ExpectationTarget(ExpectationTarget::TYPE_ACTION_ADDED, 'foo');

        $expectation = new Expectation($mockery, $target);

        $this->expectException(ExpectationArgsRequired::class);

        $expectation->withNoArgs();
    }

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

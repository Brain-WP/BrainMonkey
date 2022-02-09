<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation\Exception;

use Brain\Monkey\Expectation\Exception\Exception;
use Brain\Monkey\Tests\UnitTestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testBecauseOf()
    {
        $inner = new \RuntimeException('Foo', 123);
        $exception = Exception::becauseOf($inner);

        static::assertInstanceOf(Exception::class, $exception);
        static::assertSame('Foo', $exception->getMessage());
        static::assertSame(123, $exception->getCode());
        static::assertSame($inner, $exception->getPrevious());
    }
}

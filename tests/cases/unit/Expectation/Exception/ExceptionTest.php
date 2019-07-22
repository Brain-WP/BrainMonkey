<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests\Unit\Expectation\Exception;


use Brain\Monkey\Expectation\Exception\Exception;
use Brain\Monkey\Tests\UnitTestCase;

class ExceptionTest extends UnitTestCase
{

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

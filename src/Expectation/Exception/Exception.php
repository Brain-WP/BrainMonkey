<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation\Exception;

use Brain\Monkey\Exception as BaseException;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class Exception extends BaseException
{
    /**
     * @param \Exception $exception
     * @return static
     */
    public static function becauseOf(\Exception $exception)
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new static($exception->getMessage(), (int)$exception->getCode(), $exception);
    }
}

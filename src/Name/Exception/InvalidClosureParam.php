<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Name\Exception;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class InvalidClosureParam extends Exception
{
    const CODE_INVALID_NAME = 1;
    const CODE_INVALID_TYPE = 2;
    const CODE_MULTIPLE_VARIADIC = 3;

    /**
     * @param mixed $name
     * @return static
     */
    public static function forInvalidName($name)
    {
        if (!is_string($name)) {
            $name = is_object($name)
                ? 'instance of ' . get_class($name)
                : 'variable of type ' . gettype($name);
        }

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf('%s is not a valid function argument name.', $name),
            self::CODE_INVALID_NAME
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @return static
     */
    public static function forInvalidType($type, $name)
    {
        assert(is_string($type));
        assert(is_string($name));

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf('%s is not a valid function argument type for argument %s.', $type, $name),
            self::CODE_INVALID_TYPE
        );
    }

    /**
     * @param string $name
     * @return static
     */
    public static function forMultipleVariadic($name)
    {
        assert(is_string($name));

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                '%s is a variadic argument for a function that already has a variadic argument.',
                $name
            ),
            self::CODE_MULTIPLE_VARIADIC
        );
    }
}

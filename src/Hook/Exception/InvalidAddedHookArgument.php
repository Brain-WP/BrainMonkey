<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Hook\Exception;

use Brain\Monkey\Hook\HookStorage;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class InvalidAddedHookArgument extends InvalidHookArgument
{
    const CODE_WRONG_ARGS_COUNT = 1;
    const CODE_MISSING_CALLBACK = 2;
    const CODE_INVALID_PRIORITY = 3;
    const CODE_INVALID_ACCEPTED_ARGS = 4;

    /**
     * @param string $type
     * @return static
     */
    public static function forWrongArgumentsCount($type)
    {
        assert(is_string($type));

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                '"%s" must be called at with hook name and at maximum three other arguments: '
                . 'callback, priority, and accepted args num.',
                ($type === HookStorage::ACTIONS) ? "add_action" : "add_filter"
            ),
            self::CODE_WRONG_ARGS_COUNT
        );
    }

    /**
     * @param string $type
     * @return static
     */
    public static function forMissingCallback($type)
    {
        assert(is_string($type));

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                'A callback parameter is required for "%s".',
                ($type === HookStorage::ACTIONS) ? "add_action" : "add_filter"
            ),
            self::CODE_MISSING_CALLBACK
        );
    }

    /**
     * @param string $type
     * @return static
     */
    public static function forInvalidPriority($type)
    {
        assert(is_string($type));

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                'Priority parameter passed to "%s" must be a number.',
                ($type === HookStorage::ACTIONS) ? "add_action" : "add_filter"
            ),
            self::CODE_INVALID_PRIORITY
        );
    }

    /**
     * @param string $type
     * @return static
     */
    public static function forInvalidAcceptedArgs($type)
    {
        assert(is_string($type));

        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                'Accepted args number parameter passed to "%s" must be an integer.',
                ($type === HookStorage::ACTIONS) ? "add_action" : "add_filter"
            ),
            self::CODE_INVALID_ACCEPTED_ARGS
        );
    }
}

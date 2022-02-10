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
class InvalidHookArgument extends Exception
{
    /**
     * @param mixed $type
     * @return static
     */
    public static function forInvalidType($type)
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                'HookStorage hook type must either HookStorage::ACTIONS or '
                . 'HookStorage::FILTERS, got %s.',
                is_object($type) ? ' instance of ' . get_class($type) : gettype($type)
            )
        );
    }

    /**
     * @param mixed $type
     * @return static
     */
    public static function forInvalidHook($type)
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                'Hook name must be in a string, got %s.',
                is_object($type) ? ' instance of ' . get_class($type) : gettype($type)
            )
        );
    }

    /**
     * @param string $key
     * @param string $type
     * @return static
     */
    public static function forEmptyArguments($key, $type)
    {
        assert(is_string($key));
        assert(is_string($type));

        $function = $missing = '';

        switch ($type) {
            case HookStorage::ACTIONS:
                $missing = 'callback';
                $function = ($key === HookStorage::ADDED) ? "'add_action'" : "'do_action'";
                break;
            case HookStorage::FILTERS:
                $missing = $key === HookStorage::ADDED ? 'callback' : 'first';
                $function = ($key === HookStorage::ADDED) ? "'add_filter'" : "'apply_filters'";
                break;
        }
        /** @psalm-suppress UnsafeInstantiation */
        return new static(
            sprintf(
                'Missing %s required argument for %s.',
                $missing,
                $function
            )
        );
    }
}

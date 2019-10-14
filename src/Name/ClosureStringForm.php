<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Name;

use Brain\Monkey\Name\Exception\InvalidCallable;
use Brain\Monkey\Name\Exception\InvalidClosureParam;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class ClosureStringForm
{

    const CLOSURE_PATTERN = '/^(static\s+)?function\s*\((.*?)\)$/';

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $closure_string
     * @return string
     */
    public static function normalizeString($closure_string)
    {
        if (
            ! is_string($closure_string)
            || ! preg_match(self::CLOSURE_PATTERN, trim($closure_string), $matches)
        ) {
            throw InvalidCallable::forCallable($closure_string);
        }

        $raw_params = trim($matches[2]);
        $static = trim($matches[1]);

        $normalized = $static ? 'static function (' : 'function (';

        if ( ! $raw_params) {
            return "{$normalized})";
        }

        $variadic = false;
        $params = explode(',', $raw_params);

        $normalized = array_reduce($params, function ($normalized, $param_name) use (&$variadic) {

            $param = ClosureParamStringForm::fromString($param_name);

            $is_variadic = $param->isVariadic();
            if ($variadic && $is_variadic) {
                throw InvalidClosureParam::forMultipleVariadic($param_name);
            }

            $is_variadic and $variadic = true;

            return $normalized.(string)$param.', ';

        }, $normalized);

        return rtrim($normalized, ', ').')';
    }

    /**
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->name = $this->buildName($closure);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param \Brain\Monkey\Name\ClosureStringForm $name
     * @return bool
     */
    public function equals(ClosureStringForm $name)
    {
        return $this->__toString() === (string)$name;
    }

    /**
     * Checks the name of a function and throw an exception if is not valid.
     * When name is valid returns an array of the name itself and its namespace parts.
     *
     * @param \Closure $closure
     * @return string
     */
    private function buildName(\Closure $closure)
    {
        $reflection = new \ReflectionFunction($closure);

        // Quite hackish, but it seems there's no better way to get if a closure is static
        $bind = @\Closure::bind($closure, new \stdClass);
        $static =
            $bind === null
            || (new \ReflectionFunction($bind))->getClosureThis() === null;

        $arguments = array_map('strval', array_map(
            [ClosureParamStringForm::class, 'fromReflectionParameter'],
            $reflection->getParameters()
        ));

        $name = $static ? 'static function (' : 'function (';

        return $name.implode(', ', $arguments).')';
    }
}
<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Names;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class ClosureStringForm
{

    const VALID_PARAM_PATTERN = '/^\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

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
            || ! preg_match('/^function\((.*?)\)$/', trim($closure_string), $matches)
        ) {
            return '';
        }

        $raw_params = trim($matches[1]);

        if ( ! $raw_params) {
            return "function()";
        }

        $params = array_map('trim', explode(',', $raw_params));
        $normalized = 'function(';
        $had_variadic = false;

        foreach ($params as $param) {
            $variadic = substr($param, 0, 3) === '...';
            if ($variadic && $had_variadic) {
                return '';
            }
            if ($variadic) {
                $had_variadic = true;
                $param = ltrim($param, '. ');
            }
            if ( ! preg_match(self::VALID_PARAM_PATTERN, $param)) {
                return '';
            }
            $normalized .= $variadic ? "...{$param}, " : "{$param}, ";
        }

        return trim($normalized, ', ').')';
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
    public function name()
    {
        return $this->name;
    }

    /**
     * @param \Brain\Monkey\Names\ClosureStringForm $name
     * @return bool
     */
    public function equals(ClosureStringForm $name)
    {
        return $this->name() === $name->name();
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
        $arguments = $reflection->getParameters();
        $name = 'function(';
        foreach ($arguments as $argument) {
            $n = '$'.$argument->getName().', ';
            $argument->isVariadic() and $n = "...{$n}";
            $name .= $n;
        }

        return trim($name, ', ').')';
    }
}
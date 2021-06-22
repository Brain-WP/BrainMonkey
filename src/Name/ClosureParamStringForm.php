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

use Brain\Monkey\Name\Exception\InvalidClosureParam;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClosureParamStringForm
{

    const PARAM_SUBPATTERN = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*';

    const VALID_PARAM_PATTERN = '/^' . self::PARAM_SUBPATTERN . '$/';

    const REFLECTION_PARAM_PATTERN = '/\[\s\<\w+?>\s(' . self::PARAM_SUBPATTERN . ')/s';

    private $param_name;
    /**
     * @var string
     */
    private $type_name;
    /**
     * @var bool
     */
    private $variadic;

    /**
     * @param string $param
     * @return static
     */
    public static function fromString($param)
    {
        $param = trim($param);

        $variadic = substr_count($param, '...') === 1;
        $variadic and $param = str_replace('.', '', $param);
        $parts = array_filter(explode(' ', $param));
        $count = count($parts);

        if ($count !== 2 && $count !== 1) {
            throw InvalidClosureParam::forInvalidName($param);
        }

        $name = array_pop($parts);
        $type = $parts ? ltrim(array_pop($parts), '\\') : '';

        strpos($name, '$') === 0 and $name = substr($name, 1);

        if ($name && ! preg_match(self::VALID_PARAM_PATTERN, $name)) {
            throw InvalidClosureParam::forInvalidName($name);
        }

        if ($type && ! preg_match(self::VALID_PARAM_PATTERN, $type)) {
            throw InvalidClosureParam::forInvalidType($type, $name);
        }

        return new static($name, $type, $variadic);
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return static
     */
    public static function fromReflectionParameter(\ReflectionParameter $parameter)
    {
        $type = '';
        if (PHP_MAJOR_VERSION >= 7) {
            if ($parameter->hasType()) {
                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType) {
                    // PHP >= 7.1.
                    $type = $type->getName();
                }

                // In PHP 7.0 the ReflectionType::__toString() method will retrieve the type.
                $type = ltrim($type, '\\');
            }
        } else {
            preg_match(self::REFLECTION_PARAM_PATTERN, $parameter->__toString(), $matches);
            if (isset($matches[1])) {
                $type = $matches[1];
            }
        }

        return new static($parameter->getName(), $type, $parameter->isVariadic());
    }

    /**
     * @param string $param_name
     * @param string $type_name
     * @param bool   $variadic
     */
    private function __construct($param_name, $type_name = '', $variadic = false)
    {
        if ( ! is_string($param_name) || ! $param_name) {
            throw InvalidClosureParam::forInvalidName($param_name);
        }

        $this->param_name = $param_name;
        $this->type_name = $type_name;
        $this->variadic = $variadic;
    }

    /**
     * @param \Brain\Monkey\Name\ClosureParamStringForm $param
     * @return bool
     */
    public function equals(ClosureParamStringForm $param)
    {
        return $this->__toString() === (string)$param;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = $this->type_name ? "{$this->type_name} " : '';
        $this->variadic and $string .= '...';
        $string .= '$'.$this->param_name;

        return $string;
    }

    /**
     * @return bool
     */
    public function isVariadic()
    {
        return $this->variadic;
    }
}

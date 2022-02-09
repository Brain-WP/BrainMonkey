<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Name;

use Brain\Monkey\Name\Exception\InvalidClosureParam;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClosureParamStringForm
{
    const PARAM_SUBPATTERN = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*';
    const VALID_PARAM_PATTERN = '/^' . self::PARAM_SUBPATTERN . '$/';
    const REFLECTION_PARAM_PATTERN = '/\[\s\<\w+?>\s(' . self::PARAM_SUBPATTERN . ')/s';

    /**
     * @var string
     */
    private $paramName;

    /**
     * @var string
     */
    private $typeName;

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

        if ($name && !preg_match(self::VALID_PARAM_PATTERN, $name)) {
            throw InvalidClosureParam::forInvalidName($name);
        }

        if ($type && !preg_match(self::VALID_PARAM_PATTERN, $type)) {
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
        $paramName = $parameter->getName();
        $isVariadic = $parameter->isVariadic();

        if (PHP_MAJOR_VERSION < 7) {
            preg_match(self::REFLECTION_PARAM_PATTERN, $parameter->__toString(), $matches);
            $type = empty($matches[1]) ? '' : $matches[1];

            return new static($paramName, $type, $isVariadic);
        }

        $type = '';
        if ($parameter->hasType()) {
            $type = $parameter->getType();
            if ($type instanceof \ReflectionNamedType) {
                // PHP >= 7.1
                $type = $type->getName();
            }

            // In PHP 7.0 the ReflectionType::__toString() method will retrieve the type.
            $type = ltrim($type, '\\');
        }

        return new static($paramName, $type, $isVariadic);
    }

    /**
     * @param string $paramName
     * @param string $typeName
     * @param bool $variadic
     */
    private function __construct($paramName, $typeName = '', $variadic = false)
    {
        if (!is_string($paramName) || !$paramName) {
            throw InvalidClosureParam::forInvalidName($paramName);
        }

        $this->paramName = $paramName;
        $this->typeName = $typeName;
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
        $string = $this->typeName ? "{$this->typeName} " : '';
        $this->variadic and $string .= '...';
        $string .= '$' . $this->paramName;

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

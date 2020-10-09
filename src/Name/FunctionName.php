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


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class FunctionName
{

    const VALID_NAME_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    /**
     * @var string
     */
    private $function_name = '';

    /**
     * @var string
     */
    private $namespace = '';

    /**
     * @param string $function_name
     */
    public function __construct($function_name)
    {
        list($this->function_name, $this->namespace) = $this->parseName($function_name);
    }

    /**
     * @return string
     */
    public function fullyQualifiedName()
    {
        return ltrim("{$this->namespace}\\{$this->function_name}", '\\');
    }

    /**
     * @return string
     */
    public function shortName()
    {
        return $this->function_name;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param \Brain\Monkey\Name\FunctionName $name
     * @return bool
     */
    public function equals(FunctionName $name)
    {
        return $this->fullyQualifiedName() === $name->fullyQualifiedName();
    }

    /**
     * Checks the name of a function and throw an exception if is not valid.
     * When name is valid returns an array of the name itself and its namespace parts.
     *
     * @param mixed $function_name
     * @return string[]
     */
    private function parseName($function_name)
    {
        $chunks = is_string($function_name) ? explode('\\', ltrim($function_name, '\\')) : null;
        $valid = $chunks ? preg_filter(self::VALID_NAME_PATTERN, '$0', $chunks) : null;

        if ( ! $valid || $valid !== $chunks) {
            $name = is_string($function_name)
                ? "'{$function_name}'"
                : 'Variable of type '.gettype($function_name);

            throw Exception\InvalidName::forFunction($name);
        }

        return [array_pop($chunks), implode('\\', $chunks)];
    }
}

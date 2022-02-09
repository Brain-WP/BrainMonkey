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

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class FunctionName
{
    const VALID_NAME_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    /**
     * @var string
     */
    private $functionName = '';

    /**
     * @var string
     */
    private $namespace = '';

    /**
     * @param string $functionName
     */
    public function __construct($functionName)
    {
        list($this->functionName, $this->namespace) = $this->parseName($functionName);
    }

    /**
     * @return string
     */
    public function fullyQualifiedName()
    {
        return ltrim("{$this->namespace}\\{$this->functionName}", '\\');
    }

    /**
     * @return string
     */
    public function shortName()
    {
        return $this->functionName;
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
     * @param mixed $functionName
     * @return string[]
     */
    private function parseName($functionName)
    {
        $chunks = is_string($functionName) ? explode('\\', ltrim($functionName, '\\')) : null;
        $valid = $chunks ? preg_filter(self::VALID_NAME_PATTERN, '$0', $chunks) : null;

        if (!$valid || $valid !== $chunks) {
            $name = is_string($functionName)
                ? "'{$functionName}'"
                : 'Variable of type ' . gettype($functionName);

            throw Exception\InvalidName::forFunction($name);
        }

        return [array_pop($chunks), implode('\\', $chunks)];
    }
}

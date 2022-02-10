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
final class ClassName
{
    /**
     * @var FunctionName
     */
    private $functionName;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        try {
            $this->functionName = new FunctionName($className);
        } catch (Exception\InvalidName $exception) {
            throw Exception\InvalidName::forClass($className);
        }
    }

    /**
     * @return string
     */
    public function fullyQualifiedName()
    {
        return $this->functionName->fullyQualifiedName();
    }

    /**
     * @return string
     */
    public function shortName()
    {
        return $this->functionName->shortName();
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->functionName->getNamespace();
    }

    /**
     * @param ClassName $name
     * @return bool
     */
    public function equals(ClassName $name)
    {
        return $this->fullyQualifiedName() === $name->fullyQualifiedName();
    }
}

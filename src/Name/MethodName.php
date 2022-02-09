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
final class MethodName
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $methodName
     */
    public function __construct($methodName)
    {
        try {
            $functionName = new FunctionName($methodName);
        } catch (Exception\InvalidName $exception) {
            throw Exception\InvalidName::forMethod($methodName);
        }

        if ($functionName->getNamespace()) {
            throw Exception\InvalidName::forMethod($methodName);
        }

        $this->name = $functionName->shortName();
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @param \Brain\Monkey\Name\MethodName $name
     * @return bool
     */
    public function equals(MethodName $name)
    {
        return $this->name() === $name->name();
    }
}

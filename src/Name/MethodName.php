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
final class MethodName
{

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $method_name
     */
    public function __construct($method_name)
    {
        try {
            $function_name = new FunctionName($method_name);
        } catch (Exception\InvalidName $e) {
            throw Exception\InvalidName::forMethod($method_name);
        }

        if ($function_name->getNamespace()) {
            throw Exception\InvalidName::forMethod($method_name);
        }

        $this->name = $function_name->shortName();
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
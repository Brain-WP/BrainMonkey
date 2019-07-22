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
final class ClassName
{

    /**
     * @var \Brain\Monkey\Name\FunctionName
     */
    private $function_name;

    /**
     * @param string $class_name
     */
    public function __construct($class_name)
    {
        try {
            $this->function_name = new FunctionName($class_name);
        } catch (Exception\InvalidName $e) {
            throw Exception\InvalidName::forClass($class_name);
        }
    }

    /**
     * @return string
     */
    public function fullyQualifiedName()
    {
        return $this->function_name->fullyQualifiedName();
    }

    /**
     * @return string
     */
    public function shortName()
    {
        return $this->function_name->shortName();
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->function_name->getNamespace();
    }

    /**
     * @param \Brain\Monkey\Name\ClassName $name
     * @return bool
     */
    public function equals(ClassName $name)
    {
        return $this->fullyQualifiedName() === $name->fullyQualifiedName();
    }
}
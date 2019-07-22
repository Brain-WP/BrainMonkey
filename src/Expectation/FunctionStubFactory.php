<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation;

use Brain\Monkey\Name\FunctionName;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionStubFactory
{

    const SCOPE_STUB        = 'a stub';
    const SCOPE_EXPECTATION = 'an expectation';

    /**
     * @var array
     */
    private $storage = [];

    /**
     * @param \Brain\Monkey\Name\FunctionName $name
     * @param string                          $scope
     * @return \Brain\Monkey\Expectation\FunctionStub
     */
    public function create(FunctionName $name, $scope)
    {
        $stored_type = $this->storedType($name);

        if ( ! $stored_type) {

            $stub = new FunctionStub($name);
            $this->storage[$name->fullyQualifiedName()] = [$stub, $scope];

            return $stub;
        }

        if ($scope !== $stored_type) {
            throw new Exception\Exception(
                sprintf(
                    'It was not possible to create %s for function "%s" because %s for it already exists.',
                    $scope,
                    $name->fullyQualifiedName(),
                    $stored_type
                )
            );
        }

        list($stub) = $this->storage[$name->fullyQualifiedName()];

        return $stub;
    }

    /**
     * @param \Brain\Monkey\Name\FunctionName $name
     * @return bool
     */
    public function has(FunctionName $name)
    {
        return array_key_exists($name->fullyQualifiedName(), $this->storage);
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->storage = [];
    }

    /**
     * @param \Brain\Monkey\Name\FunctionName $name
     * @return string
     */
    private function storedType(FunctionName $name)
    {
        if ( ! $this->has($name)) {
            return '';
        }

        list(, $stored_type) = $this->storage[$name->fullyQualifiedName()];

        return $stored_type;
    }
}
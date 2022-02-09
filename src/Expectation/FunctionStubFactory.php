<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation;

use Brain\Monkey\Name\FunctionName;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionStubFactory
{
    const SCOPE_STUB = 'a stub';
    const SCOPE_EXPECTATION = 'an expectation';

    /**
     * @var array
     */
    private $storage = [];

    /**
     * @param \Brain\Monkey\Name\FunctionName $name
     * @param string $scope
     * @return \Brain\Monkey\Expectation\FunctionStub
     */
    public function create(FunctionName $name, $scope)
    {
        $storedType = $this->storedType($name);

        if (!$storedType) {
            $stub = new FunctionStub($name);
            $this->storage[$name->fullyQualifiedName()] = [$stub, $scope];

            return $stub;
        }

        if ($scope !== $storedType) {
            throw new Exception\Exception(
                sprintf(
                    'It was not possible to create %s for function "%s" '
                    . 'because %s for it already exists.',
                    $scope,
                    $name->fullyQualifiedName(),
                    $storedType
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
        if (!$this->has($name)) {
            return '';
        }

        list(, $storedType) = $this->storage[$name->fullyQualifiedName()];

        return $storedType;
    }
}

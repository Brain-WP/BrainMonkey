<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Name\Exception;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class NotInvokableObjectAsCallback extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Only closures and invokable objects can be used as callbacks for hooks.'
        );
    }
}

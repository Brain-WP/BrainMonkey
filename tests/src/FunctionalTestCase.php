<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionalTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown()
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}
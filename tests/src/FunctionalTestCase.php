<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Tests;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * @package Brain\Monkey\Tests
 * @license http://opensource.org/licenses/MIT MIT
 */
class FunctionalTestCase extends TestCase
{
    /**
     * @before
     */
    protected function setUpFixtures()
    {
        parent::setUp();
        Monkey\setUp();
    }

    /**
     * @after
     */
    protected function tearDownFixtures()
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}

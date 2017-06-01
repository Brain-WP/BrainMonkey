<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */

$autoload_path = dirname(__DIR__).'/vendor/';
if (! file_exists($autoload_path.'autoload.php')) {
    die('Please install via composer before running tests.');
}

require_once $autoload_path.'phpunit/phpunit/src/Framework/Assert/Functions.php';
require_once $autoload_path.'autoload.php';
unset($autoload_path);

\Brain\Monkey::setUp();

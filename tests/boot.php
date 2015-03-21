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

$autoload_path = dirname(__DIR__).'/vendor/autoload.php';
if (! file_exists($autoload_path)) {
    die('Please install via composer before running tests.');
}
require_once $autoload_path;
require_once dirname(__DIR__).'/vendor/antecedent/patchwork/Patchwork.php';

$helpers_path = dirname($autoload_path).'/phpunit/phpunit/src/Framework/Assert/Functions.php';
if (! file_exists($helpers_path)) {
    die('Please install via composer with dev option before running tests.');
}
require_once $helpers_path;

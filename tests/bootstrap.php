<?php

/*
 * This file is part of the Brain\Monkey package.
 *
 * (c) Juliette Reinders Folmer, Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$autoload = realpath(__DIR__ . '/../vendor/autoload.php');

if (!$autoload || !file_exists($autoload)) {
    echo 'Autoload file not found. Please run `composer install`.';
    die(1);
}

require_once $autoload;
unset($autoload);

// PHPUnit cross version compatibility.
if (
    class_exists('PHPUnit_Framework_Error') === true
    && class_exists('PHPUnit\Framework\Error\Error') === false
) {
    class_alias('PHPUnit_Framework_Error', 'PHPUnit\Framework\Error\Error');
}

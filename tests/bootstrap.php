<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Juliette Reinders Folmer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$autoload = realpath(__DIR__ . '/../vendor/autoload.php');

if (file_exists($autoload)) {
    require_once $autoload;
} else {
    echo 'Autoload file not found. Please run `composer install`.';
    die(1);
}

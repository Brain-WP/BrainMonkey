<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @package Brain\Monkey
 */

if (function_exists('Patchwork\redefine')) {
    return;
}

if (file_exists(dirname(dirname(dirname(__DIR__))) . "/antecedent/patchwork/Patchwork.php")) {
    /** @psalm-suppress MissingFile */
    require_once dirname(dirname(dirname(__DIR__))) . "/antecedent/patchwork/Patchwork.php";
} elseif (file_exists(dirname(__DIR__) . "/vendor/antecedent/patchwork/Patchwork.php")) {
    /** @psalm-suppress MissingFile */
    require_once dirname(__DIR__) . "/vendor/antecedent/patchwork/Patchwork.php";
}

if (!function_exists('Patchwork\redefine')) {
    throw new \Brain\Monkey\Exception(
        'Brain Monkey was unable to load Patchwork. '
        . 'Please require Patchwork.php by yourself before running tests.'
    );
}

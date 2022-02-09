<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation\Exception;

/**
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class MissedPatchworkReplace extends Exception
{
    /**
     * @param string $functionName
     * @return static
     */
    public static function forFunction($functionName)
    {
        return new static(
            "Patchwork was not able to replace '{$functionName}', try to load Patchwork earlier."
        );
    }
}

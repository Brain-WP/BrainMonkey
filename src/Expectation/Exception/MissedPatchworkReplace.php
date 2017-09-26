<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation\Exception;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package BrainMonkey
 * @license http://opensource.org/licenses/MIT MIT
 */
class MissedPatchworkReplace extends Exception
{

    /**
     * @param string $function_name
     * @return static
     */
    public static function forFunction($function_name)
    {
        return new static(
            "Patchwork was not able to replace '{$function_name}', try to load Patchwork earlier."
        );
    }

}
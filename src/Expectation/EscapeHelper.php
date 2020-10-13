<?php
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation;

/**
 * Helper functions used to get an escaping that is "similar enough" to WordPress functions,
 * without adding too much complexity.
 *
 * For edge cases consumers can either override the downstream functions that make use of this, or
 * tests in integration.
 */
class EscapeHelper
{

    /**
     * @param string $text
     * @return string
     */
    public static function esc($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param string $text
     * @return void
     */
    public static function escAndEcho($text)
    {
        print static::esc($text);

    }

    /**
     * @param string $url
     * @return string
     */
    public static function escUrlRaw($url)
    {
        if ( ! parse_url($url, PHP_URL_SCHEME)) {
            $url = "http://{$url}";
        }

        return $url;
    }

    /**
     * @param string $url
     * @return string
     */
    public static function escUrl($url)
    {
        return str_replace(['&amp;', "'"], ['&#038;', '&#039;'], static::escUrlRaw($url));
    }

    /**
     * @param string $text
     * @return string
     */
    public static function escXml($text)
    {
        $text        = html_entity_decode($text, ENT_QUOTES | ENT_XML1 | ENT_XHTML, 'UTF-8'); // Undo existing entities.
        $cdata_regex = '\<\!\[CDATA\[.*?\]\]\>';
        $regex       = "
            `
                (?=.*?{$cdata_regex})                 # lookahead that will match anything followed by a CDATA Section
                (?<non_cdata_followed_by_cdata>(.*?)) # the 'anything' matched by the lookahead
                (?<cdata>({$cdata_regex}))            # the CDATA Section matched by the lookahead
            |                                         # alternative
                (?<non_cdata>(.*))                    # non-CDATA Section
            `sx";

        return (string) preg_replace_callback(
            $regex,
            static function($matches) {
                if ( ! $matches[0]) {
                    return '';
                }

                if ( ! empty($matches['non_cdata'])) {
                    // Escape HTML entities in the non-CDATA Section.
                    return htmlspecialchars($matches['non_cdata'], ENT_XML1, 'UTF-8', false);
                }

                // Return the CDATA Section unchanged, escape HTML entities in the rest.
                return htmlspecialchars($matches['non_cdata_followed_by_cdata'], ENT_XML1, 'UTF-8', false) . $matches['cdata'];
            },
            $text
        );
    }
}

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

class UrlsHelper
{
    const DEFAULT_DOMAIN = 'example.org';

    /**
     * @var string
     */
    private $domain;

    /**
     * @var bool|null
     */
    private $use_https;

    /**
     * @param $domain
     * @param $use_https
     */
    public function __construct($domain = null, $use_https = null)
    {
        $this->domain = (is_string($domain) && $domain !== '') ? $domain : self::DEFAULT_DOMAIN;
        $this->use_https = ($use_https === null)
            ? null
            : (bool)filter_var($use_https, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param $base_path
     * @param $def_schema
     * @return \Closure
     */
    public function stubUrlForSiteCallback($base_path = '', $def_schema = null)
    {
        return function ($site_id, $path = '', $schema = null) use ($base_path, $def_schema) {
            ($def_schema && $schema === null) and $schema = $def_schema;
            return $this->build_url(
                $this->build_relative_path($base_path, $path),
                $this->determineSchema($schema)
            );
        };
    }

    /**
     * @param $base_path
     * @param $def_schema
     * @param $use_schema_arg
     * @return \Closure
     */
    public function stubUrlCallback($base_path = '', $def_schema = null, $use_schema_arg = true)
    {
        return function ($path = '', $schema = null) use ($base_path, $def_schema, $use_schema_arg) {
            ($def_schema && $schema === null) and $schema = $def_schema;
            return $this->build_url(
                $this->build_relative_path($base_path, $path),
                $this->determineSchema($use_schema_arg ? $schema : null)
            );
        };
    }

    /**
     * @param $relative
     * @param $schema
     * @return mixed|string
     */
    private function build_url($relative, $schema)
    {
        return ($schema === null) ? ($relative ?: '/') : $schema . $this->domain . $relative;
    }

    /**
     * @param $base_path
     * @param $path
     * @return string
     */
    private function build_relative_path($base_path, $path)
    {
        $path = (($path !== '') && is_string($path))
            ? '/' . ltrim($path, '/')
            : '';
        $base_path = (($base_path !== '') && is_string($base_path))
            ? '/' . trim($base_path, '/')
            : '';

        return $base_path . $path;
    }

    /**
     * @param $schema_argument
     * @return string|null
     */
    private function determineSchema($schema_argument = null)
    {
        if ($schema_argument === 'relative') {
            return null;
        }

        $use_https = $this->use_https;
        $is_ssl = function_exists('is_ssl') ? is_ssl() : true;
        if ($use_https === null && !in_array($schema_argument, ['http', 'https'], true)) {
            $use_https = $is_ssl;
            if (
                !$use_https
                && in_array($schema_argument, ['admin', 'login', 'login_post', 'rpc'])
                && function_exists('force_ssl_admin')
            ) {
                $use_https = force_ssl_admin();
            }
        }
        if ($schema_argument === 'http') {
            $use_https = false;
        }

        return $use_https ? 'https://' : 'http://';
    }
}

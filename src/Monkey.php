<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain;

use Brain\Monkey\WP\Hooks;
use Brain\Monkey\Functions;
use Patchwork;
use Mockery;
use ReflectionClass as Reflection;
use ReflectionMethod as M;
use RuntimeException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 *
 * @method Functions when()
 * @method \Mockery\Expectation expect()
 */
class Monkey
{
    /**
     * @var array|null
     */
    private $proxied;

    /**
     * Include WordPress functions file, only needed to mock WordPress hooks.
     */
    public static function setUp()
    {
        if (function_exists('Patchwork\redefine')) {
            return;
        }

        $patchwork = '/antecedent/patchwork/Patchwork.php';

        if (file_exists(dirname(dirname(dirname(__DIR__))).$patchwork)) {
            /** @noinspection PhpIncludeInspection */
            @require_once dirname(dirname(dirname(__DIR__))).$patchwork; // normal installation
        } elseif (file_exists(dirname(__DIR__)."/vendor{$patchwork}")) {
            /** @noinspection PhpIncludeInspection */
            @require_once dirname(__DIR__)."/vendor{$patchwork}"; // root installation
        }
        if (! function_exists('Patchwork\redefine')) {
            throw new RuntimeException(
                'Brain Monkey was unable to load Patchwork. Please require Patchwork.php by yourself before running tests.'
            );
        }
    }

    /**
     * Include WordPress functions file, only needed to mock WordPress hooks.
     */
    public static function setUpWP()
    {
        self::setUp();
        require_once dirname(__DIR__).'/inc/wp-functions.php';
    }

    /**
     * Clean up Functions and Hooks statics: is always needed when using this class.
     */
    public static function tearDown()
    {
        Functions::__flush();
        Patchwork\restoreAll();
        Mockery::close();
    }

    /**
     * Clean up Functions and Hooks statics: is always needed when using this class.
     */
    public static function tearDownWP()
    {
        Hooks::tearDown();
        self::tearDown();
    }

    /**
     * An alias for Hooks::instance(Hooks::ACTION), allows to only use this class inside tests.
     *
     * @return \Brain\Monkey\WP\Actions
     */
    public static function actions()
    {
        return Hooks::instance(Hooks::ACTION);
    }

    /**
     * An alias for Hooks::instance(Hooks::FILTER), allows to only use this class inside tests.
     *
     * @return \Brain\Monkey\WP\Filters
     */
    public static function filters()
    {
        return Hooks::instance(Hooks::FILTER);
    }

    /**
     * Returns an instance of current class, that thanks to __call() implementation allows to call
     * static Functions class methods on the returned instance.
     * This way this method allows exactly same syntax of actions() and filters().
     *
     * @return \Brain\Monkey
     */
    public static function functions()
    {
        return new self('Brain\Monkey\Functions');
    }

    /**
     * Constructor.
     *
     * Passing a target class, it will be possible to call dynamically on the obtained instance
     * methods that will be proxied to target class static methods.
     *
     * @param string|null $target
     */
    public function __construct($target = null)
    {
        if (is_string($target) && class_exists($target)) {
            $this->proxied[$target] = array_map(
                function ($method) {
                    return $method->name;
                },
                (new Reflection($target))->getMethods(M::IS_STATIC | M::IS_PUBLIC)
            );
        }
    }

    /**
     * When a target object is set, allows to call static methods on target class by calling
     * same-named dynamic methods on this class.
     * Mainly used to allows for functions() same syntax of actions() and filters().
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        if (! empty($this->proxied) && in_array($name, reset($this->proxied), true)) {
            return call_user_func_array([key($this->proxied), $name], $arguments);
        }
        $backtrace = debug_backtrace(0, 2);
        trigger_error(
            sprintf(
                "Call to undefined method <b>%s()</b> in <b>%s</b> line <b>%d</b>.",
                __CLASS__.'::'.$name,
                $backtrace[1]['file'],
                $backtrace[1]['line']
            ),
            E_USER_ERROR
        );
    }
}

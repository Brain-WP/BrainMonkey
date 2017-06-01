<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\WP;

use Brain\Monkey\MockeryBridge;
use Mockery;
use Closure;
use InvalidArgumentException;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
abstract class Hooks
{
    const ACTION = 'action';
    const FILTER = 'filter';

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var array
     */
    private static $names = [];

    /**
     * @var array
     */
    private static $classes = [
        self::ACTION => 'Brain\Monkey\WP\Actions',
        self::FILTER => 'Brain\Monkey\WP\Filters',
    ];

    private static $sanitize_map = [
        '-'  => '_',
        ' '  => '_sp_',
        '/'  => '_sl_',
        '\\' => '_bs_',
        '.'  => '_po_',
        '!'  => '_es_',
        '"'  => '_q_',
        '\'' => '_sq_',
        '£'  => '_pou_',
        '$'  => '_do_',
        '%'  => '_pe_',
        '('  => '_op_',
        ')'  => '_cp_',
        '='  => '_eq_',
        '?'  => '_qm_',
        '^'  => '_ca_',
        '*'  => '_as_',
        '@'  => '_at_',
        '°'  => '_deg_',
        '#'  => '_sh_',
        '['  => '_ob_',
        ']'  => '_cb_',
        '+'  => '_and_',
        '|'  => '_pi_',
        '<'  => '_lt_',
        '>'  => '_gt_',
        ','  => '_co_',
        ';'  => '_sc_',
        '{'  => '_ocb_',
        '}'  => '_ccb_',
        '~'  => '_ti_',
    ];

    /**
     * @var bool
     */
    private static $current = false;

    /**
     * @var array
     */
    protected $hooks = [];

    /**
     * @var array
     */
    protected $done = [];

    /**
     * @var array
     */
    protected $mocks = [];

    /**
     * @param  string $name
     * @param  array  $args
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        $type = strstr($name, self::ACTION) ? self::ACTION : self::FILTER;
        $method = substr($name, 0, -1 * strlen($type));

        return call_user_func_array([self::instance($type), $method], $args);
    }

    /**
     * @param  string $type
     * @return \Brain\Monkey\WP\Actions|\Brain\Monkey\WP\Filters
     */
    public static function instance($type)
    {
        if (! isset(self::$instances[$type])) {
            $class = self::$classes[$type];
            self::$instances[$type] = new $class();
        }

        return self::$instances[$type];
    }

    public static function tearDown()
    {
        if (isset(self::$instances[self::ACTION])) {
            /** @var \Brain\Monkey\WP\Actions $actions */
            $actions = self::$instances[self::ACTION];
            $actions->clean();
            self::$instances[self::ACTION] = null;
        }
        if (isset(self::$instances[self::FILTER])) {
            /** @var \Brain\Monkey\WP\Filters $filters */
            $filters = self::$instances[self::FILTER];
            $filters->clean();
            self::$instances[self::FILTER] = null;
        }
        self::$instances = [];
        self::$names = [];
        self::$current = null;
    }

    /**
     * @return string|bool
     */
    public static function current()
    {
        $current = $name = self::$current;
        if (is_string($current) && isset(self::$names[$current])) {
            $name = self::$names[$current];
        }

        return $name;
    }

    abstract public function add();

    abstract public function remove();

    abstract public function run();

    abstract public function runRef();

    abstract public function has();

    abstract public function clean();

    /**
     * @param  string $type
     * @return bool
     */
    protected function addHook($type)
    {
        /** @var \Brain\Monkey\WP\Actions|\Brain\Monkey\WP\Filters $instance */
        $instance = self::instance($type);
        $parsed = $this->args(array_slice(func_get_args(), 1), $type);
        $data = reset($parsed);
        // hook name, e.g. 'init'
        $hook = self::sanitizeHookName($data['hook']);
        if (! isset($instance->hooks[$hook])) {
            $instance->hooks[$hook] = [];
            self::$names[$hook] = $data['hook'];
        }
        $instance->hooks[$hook][key($parsed)] = $data;
        if (isset($instance->mocks[$hook]) && isset($instance->mocks[$hook]['add'])) {
            /** @var \Mockery\Expectation $mock */
            $mock = $instance->mocks[$hook]['add'];
            call_user_func_array([$mock, "add_{$type}_{$hook}"], array_slice(func_get_args(), 2));
        }

        return true;
    }

    /**
     * @param  string $type
     * @return bool
     */
    protected function removeHook($type)
    {
        /** @var \Brain\Monkey\WP\Actions|\Brain\Monkey\WP\Filters $instance */
        $instance = self::instance($type);
        $parsed = $this->args(array_slice(func_get_args(), 1), $type, true);
        $data = reset($parsed);
        // hook name, e.g. 'init'
        $hook = self::sanitizeHookName($data['hook']);
        if (isset($instance->hooks[$hook]) && is_array($instance->hooks[$hook])) {
            $hooks = $instance->hooks[$hook];
            foreach ($hooks as $key => $hookData) {
                if ($key === key($parsed) && $hookData === $data) {
                    unset($instance->hooks[$hook][$key]);

                    return true;
                };
            }
        }

        return false;
    }

    /**
     * @param  string $type
     * @return mixed|null
     */
    protected function runHook($type)
    {
        /** @var \Brain\Monkey\WP\Actions|\Brain\Monkey\WP\Filters $instance */
        $instance = self::instance($type);
        $args = array_slice(func_get_args(), 1);
        if (empty($args) || ! is_string(reset($args))) {
            throw new LogicException("To fire a {$type} its name is required and has to be a string.");
        }
        // hook name, e.g. 'init'
        $rawHook = array_shift($args);
        // sanitized hook name, where anything that does not match [a-zA-Z0-9_] is removed.
        // this is done because hooks becomes class methods, and special chars are not allowed there
        $hook = self::sanitizeHookName($rawHook);
        if ($rawHook !== $hook && ! isset(self::$names[$hook])) {
            self::$names[$hook] = $rawHook;
        }
        // returning value is always null for actions
        $value = $type === self::FILTER && func_num_args() > 2 ? func_get_arg(2) : null;
        self::$current = $hook;
        // This will be used to mock `did_action` so we have to store the raw hook
        $instance->done[] = $rawHook;
        if (isset($instance->mocks[$hook]) && isset($instance->mocks[$hook]['run'])) {
            /** @var \Mockery\Expectation $mock */
            $mock = $instance->mocks[$hook]['run'];
            $verb = $type === self::FILTER ? 'apply' : 'do';
            $result = call_user_func_array([$mock, "{$verb}_{$type}_{$hook}"], $args);
            $value = $type === self::FILTER ? $result : null;
        }
        self::$current = false;

        return $value;
    }

    /**
     * @param  string $type
     * @return bool
     */
    protected function hasHook($type)
    {
        /** @var \Brain\Monkey\WP\Actions|\Brain\Monkey\WP\Filters $instance */
        $instance = self::instance($type);
        $hookArgs = array_slice(func_get_args(), 1);
        $hookArgsCount = count($hookArgs);
        // We are checking just if the hook has *any* callback attached
        if ($hookArgsCount === 1 && is_string($hookArgs[0])) {
            return ! empty($instance->hooks[self::sanitizeHookName($hookArgs[0])]);
        }
        $parsed = $this->args($hookArgs, $type, true);
        $data = reset($parsed);
        // hook name, e.g. 'init'
        $hook = self::sanitizeHookName($data['hook']);
        if (isset($instance->hooks[$hook]) && is_array($instance->hooks[$hook])) {
            foreach ($instance->hooks[$hook] as $key => $hookData) {
                if ($hookData === $data) {
                    return true;
                };
            }
        }

        return false;
    }

    /**
     * @param \Brain\Monkey\WP\Hooks $instance
     */
    protected function cleanInstance(Hooks $instance)
    {
        $instance->hooks = null;
        if (! empty($instance->mocks)) {
            foreach (array_keys($instance->mocks) as $key) {
                $instance->mocks[$key] = null;
            }
        }
        $instance->mocks = null;
        $instance->done = null;
    }

    /**
     * Receive variadic arguments and format an array with all the information about hook.
     * Missing values are filled with default.
     *
     * @param  array  $args
     * @param  string $type
     * @param  bool   $getId
     * @return array
     */
    private function args(array $args, $type, $getId = false)
    {
        if (empty($args)) {
            throw new InvalidArgumentException("{$type} name and callback are required.");
        }
        $hook = array_shift($args);
        if (empty($hook) || ! is_string($hook)) {
            throw new InvalidArgumentException("{$type} name must be in a string.");
        }
        $callback = empty($args) ? : array_shift($args);
        if (is_callable($callback)) {
            $getId = false;
        }
        if (($getId && ! is_string($callback)) || (! $getId && ! is_callable($callback))) {
            throw new InvalidArgumentException("A callback is required to add a {$type}.");
        }
        $callbackId = $getId ? [$callback] : $this->callbackId($callback);
        $callbackUId = $getId ? $callback.'__'.uniqid() : $callbackId[0].'__'.$callbackId[1];
        $priority = empty($args) ? 10 : array_shift($args);
        if (! is_numeric($priority)) {
            throw new InvalidArgumentException("To add a {$type} priority must be an integer.");
        }
        $argsNum = empty($args) ? 1 : array_shift($args);
        if (! is_numeric($argsNum)) {
            throw new InvalidArgumentException("To add a {$type} accepted args must be an integer.");
        }

        if ($getId && preg_match('/^\s*function\s*\(\s*\)\s*$/', $callbackId[0])) {
            $callbackId[0] = 'function()';
        }

        return [
            $callbackUId => [
                'hook'     => $hook,
                'id'       => $callbackId[0],
                'priority' => $priority,
                'args_num' => $argsNum,
            ]
        ];
    }

    /**
     * @param  callable $callback
     * @return array
     */
    private function callbackId(callable $callback)
    {
        $hash = '';
        $id = '';
        if (is_string($callback)) {
            $id = $callback;
        } elseif ($callback instanceof Closure) {
            /** @var object $callback */
            $hash = spl_object_hash($callback);
            $id = 'function()';
        } elseif (is_object($callback)) {
            /** @var object $callback */
            $hash = spl_object_hash($callback);
            $id = get_class($callback).'()';
        } elseif (is_array($callback) && is_object($callback[0])) {
            $hash = spl_object_hash($callback[0]);
            $id = get_class($callback[0])."->{$callback[1]}()";
        } elseif (is_array($callback)) {
            $id = "{$callback[0]}::{$callback[1]}()";
        }

        return [$id, $hash];
    }

    /**
     * @param  string $type
     * @param  string $hook
     * @param  string $action
     * @return \Brain\Monkey\WP\MockeryHookBridge
     */
    protected static function createBridgeFor($type, $hook, $action = 'add')
    {
        $hook = self::sanitizeHookName($hook);
        /** @var static $instance */
        $instance = self::instance($type);
        $prefix = $action;
        ($action === 'run') and $prefix = $type === self::FILTER ? 'apply' : 'do';
        $method = "{$prefix}_{$type}_{$hook}";

        $mock = null;
        is_array($instance->mocks) or $instance->mocks = [];
        if (! isset($instance->mocks[$hook]) || ! isset($instance->mocks[$hook][$action])) {
            isset($instance->mocks[$hook]) or $instance->mocks[$hook] = [];
            $mock = Mockery::mock("{$prefix}_{$hook}");
            $instance->mocks[$hook][$action] = $mock;
        }

        $mock = $instance->mocks[$hook][$action];
        $expectation = $mock->shouldReceive($method);
        $parent = $type === self::FILTER ? '\Brain\Monkey\WP\Filters' : '\Brain\Monkey\WP\Actions';

        return new MockeryHookBridge(new MockeryBridge($expectation, $parent));
    }

    /**
     * @param  string $name
     * @return string
     */
    private static function sanitizeHookName($name)
    {
        $replaced = strtr($name, self::$sanitize_map);
        $clean = preg_replace('/[^a-z0-9_]/i', '__', $replaced);
        if (is_numeric($clean[0])) {
            $clean = '_'.$clean;
        }

        return $clean;
    }
}

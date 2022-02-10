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

// Ignore this. Just a safeguard in case of WordPress + Composer broken setup.
namespace {

    if (function_exists('Brain\Monkey\setUp')) {
        return;
    }
}

namespace Brain\Monkey {

    use Mockery;
    use Patchwork;

    /**
     * Setup function to be called before _each_ unit test. This is not required to just mock
     * PHP functions without using WP features.
     *
     * @return void
     */
    function setUp()
    {
        require_once dirname(__DIR__) . '/inc/patchwork-loader.php';
        require_once dirname(__DIR__) . '/inc/wp-hook-functions.php';
        require_once dirname(__DIR__) . '/inc/wp-helper-functions.php';
    }

    /**
     * Setup function to be called after _each_ unit test. This is *always* required.
     *
     * @return void
     */
    function tearDown()
    {
        Container::instance()->reset();
        Mockery::close();
        Patchwork\restoreAll();
    }
}

namespace Brain\Monkey\Functions {

    use Brain\Monkey\Container;
    use Brain\Monkey\Expectation\EscapeHelper;
    use Brain\Monkey\Expectation\Expectation;
    use Brain\Monkey\Expectation\FunctionStubFactory;
    use Brain\Monkey\Expectation\FunctionStub;
    use Brain\Monkey\Name\FunctionName;

    /**
     * API entry-point for plain functions stub.
     *
     * Factory method: receives the name of the function to mock and returns an instance of
     * FunctionStub.
     *
     * @param string $functionName the name of the function to mock
     * @return FunctionStub
     */
    function when($functionName)
    {
        return Container::instance()
            ->functionStubFactory()
            ->create(new FunctionName($functionName), FunctionStubFactory::SCOPE_STUB);
    }

    /**
     * API method to fast & simple create multiple functions stubs.
     *
     * It does not allow adding expectations.
     *
     * The function name to create stub for can be passed as array key or as array value (with no
     * key).
     *
     * When the function name is in the key, the value can be:
     *   - a callable, in which case the function will be aliased to it
     *   - anything else, in which case a stub returning given value will be created for the
     *   function
     *
     * When the function name is in the value, and no key is set, the behavior will change based on
     * the second param:
     *   - when 2nd param is `null` (default) the created stub will return the 1st param it will
     *   receive
     *   - when 2nd param is anything else the created stub will return it
     *
     *
     * @param array $functions
     * @param mixed $defaultReturn
     * @return void
     */
    function stubs(array $functions, $defaultReturn = null)
    {
        foreach ($functions as $key => $value) {
            list($functionName, $returnValue) = is_numeric($key)
                ? [$value, $defaultReturn]
                : [$key, $value];

            assert(is_string($functionName));

            if (is_callable($returnValue)) {
                when($functionName)->alias($returnValue);
                continue;
            }

            ($returnValue === null)
                ? when($functionName)->returnArg()
                : when($functionName)->justReturn($returnValue);
        }
    }

    /**
     * API entry-point for plain functions expectations.
     *
     * Returns a Mockery Expectation object, where is possible to set all the expectations, using
     * Mockery methods.
     *
     * @param string $functionName
     * @return Expectation
     */
    function expect($functionName)
    {
        $name = new FunctionName($functionName);
        $expectation = Container::instance()
            ->expectationFactory()
            ->forFunctionExecuted($functionName);

        $factory = Container::instance()->functionStubFactory();
        if (!$factory->has($name)) {
            $factory->create($name, FunctionStubFactory::SCOPE_EXPECTATION)
                ->redefineUsingExpectation($expectation);
        }

        return $expectation;
    }

    /**
     * Stub translation functions.
     *
     * @return void
     *
     * @see EscapeHelper
     */
    function stubTranslationFunctions()
    {
        static $nCb;
        /**
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress MissingClosureReturnType
         */
        $nCb or $nCb = static function ($single, $plural, $number) {
            return ($number === 1) ? $single : $plural;
        };

        stubs(
            [
                '__',
                '_x',
                'translate',
                '_n' => $nCb,
                '_nx' => $nCb,
                'esc_html__' => [EscapeHelper::class, 'esc'],
                'esc_html_x' => [EscapeHelper::class, 'esc'],
                'esc_attr__' => [EscapeHelper::class, 'esc'],
                'esc_attr_x' => [EscapeHelper::class, 'esc'],
                'esc_html_e' => [EscapeHelper::class, 'escAndEcho'],
                'esc_attr_e' => [EscapeHelper::class, 'escAndEcho'],
            ]
        );

        when('_e')->echoArg();
        when('_ex')->echoArg();
    }

    /**
     * Stub escape functions with default behavior.
     *
     * @return void
     *
     * @see EscapeHelper
     */
    function stubEscapeFunctions()
    {
        stubs(
            [
                'esc_js' => [EscapeHelper::class, 'esc'],
                'esc_sql' => 'addslashes',
                'esc_attr' => [EscapeHelper::class, 'esc'],
                'esc_html' => [EscapeHelper::class, 'esc'],
                'esc_textarea' => [EscapeHelper::class, 'esc'],
                'esc_url' => [EscapeHelper::class, 'escUrl'],
                'esc_url_raw' => [EscapeHelper::class, 'escUrlRaw'],
                'esc_xml' => [EscapeHelper::class, 'escXml'],
            ]
        );
    }
}

namespace Brain\Monkey\Actions {

    use Brain\Monkey\Container;
    use Brain\Monkey\Hook;
    use Brain\Monkey\Expectation;

    /**
     * API entry-point for added action expectations.
     *
     * Takes the action name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $action
     * @return Expectation\Expectation
     */
    function expectAdded($action)
    {
        assert(is_string($action));

        return Container::instance()->expectationFactory()->forActionAdded($action);
    }

    /**
     * API entry-point for fired action expectations.
     *
     * Takes the action name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $action
     * @return Expectation\Expectation
     */
    function expectDone($action)
    {
        assert(is_string($action));

        return Container::instance()->expectationFactory()->forActionDone($action);
    }

    /**
     * Utility method to check if any or specific callback has been added to given action.
     *
     * Brain Monkey version of `has_action` will alias here.
     *
     * @param string $action
     * @param callable|null $callback
     * @return bool|int
     */
    function has($action, $callback = null)
    {
        assert(is_string($action));

        $type = Hook\HookStorage::ACTIONS;
        $hookStorage = Container::instance()->hookStorage();

        if ($callback === null) {
            return $hookStorage->isHookAdded($type, $action);
        }

        return $hookStorage->hookPriority($type, $action, $callback);
    }

    /**
     * Utility method to check if given action has been done.
     *
     * Brain Monkey version of `did_action` will alias here.
     *
     * @param string $action
     * @return int
     */
    function did($action)
    {
        assert(is_string($action));

        return Container::instance()->hookStorage()->isHookDone(Hook\HookStorage::ACTIONS, $action);
    }

    /**
     * Utility method to check if given action is currently being done.
     *
     * Brain Monkey version of `doing_action` will alias here.
     *
     * @param string|null $action
     * @return bool
     */
    function doing($action)
    {
        assert(($action === null) || is_string($action));

        return Container::instance()->hookRunningStack()->has($action);
    }

    /**
     * API entry-point for removed action expectations.
     *
     * Takes the action name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $action
     * @return Expectation\Expectation
     */
    function expectRemoved($action)
    {
        assert(is_string($action));

        return Container::instance()->expectationFactory()->forActionRemoved($action);
    }
}

namespace Brain\Monkey\Filters {

    use Brain\Monkey\Container;
    use Brain\Monkey\Expectation;
    use Brain\Monkey\Hook;

    /**
     * API entry-point for added filter expectations.
     *
     * Takes the filter name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $filter
     * @return Expectation\Expectation
     */
    function expectAdded($filter)
    {
        assert(is_string($filter));

        return Container::instance()->expectationFactory()->forFilterAdded($filter);
    }

    /**
     * API entry-point for applied filter expectations.
     *
     * Takes the filter name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $filter
     * @return Expectation\Expectation
     */
    function expectApplied($filter)
    {
        assert(is_string($filter));

        return Container::instance()->expectationFactory()->forFilterApplied($filter);
    }

    /**
     * Utility method to check if any or specific callback has been added to given filter.
     *
     * Brain Monkey version of `has_filter` will alias here.
     *
     * @param string $filter
     * @param callable|null $callback
     * @return false|int|bool If callback is omitted, returns boolean for whether the hook has
     *                        anything registered. When checking a specific callback, the priority
     *                        of that hook is returned, or false if the callback is not attached.
     */
    function has($filter, $callback = null)
    {
        assert(is_string($filter));

        $type = Hook\HookStorage::FILTERS;
        $hookStorage = Container::instance()->hookStorage();

        if ($callback === null) {
            return $hookStorage->isHookAdded($type, $filter);
        }

        return $hookStorage->hookPriority($type, $filter, $callback);
    }

    /**
     * Utility method to check if given filter as been applied.
     *
     * There's no WordPress function counterpart for it.
     *
     * @param string $filter
     * @return int
     */
    function applied($filter)
    {
        assert(is_string($filter));

        return Container::instance()->hookStorage()->isHookDone(Hook\HookStorage::FILTERS, $filter);
    }

    /**
     * Utility method to check if given filter is currently being done.
     *
     * Brain Monkey version of `doing_filter` will alias here.
     *
     * @param string|null $filter
     * @return bool
     */
    function doing($filter)
    {
        assert(($filter === null) || is_string($filter));

        return Container::instance()->hookRunningStack()->has($filter);
    }

    /**
     * API entry-point for removed action expectations.
     *
     * Takes the action name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $filter
     * @return Expectation\Expectation
     */
    function expectRemoved($filter)
    {
        assert(is_string($filter));

        return Container::instance()->expectationFactory()->forFilterRemoved($filter);
    }
}

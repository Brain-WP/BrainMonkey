<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Ignore this. Just a safeguard in case of WordPress + Composer broken (really broken) setup.
namespace {

    if (function_exists('Brain\Monkey\setUp')) {
        return;
    }
}

namespace Brain\Monkey {

    /**
     * Setup function to be called before _each_ unit test. This is not required to just mock
     * PHP functions without using WP features.
     */
    function setUp()
    {
        require_once dirname(__DIR__).'/inc/patchwork-loader.php';
        require_once dirname(__DIR__).'/inc/wp-hook-functions.php';
        require_once dirname(__DIR__).'/inc/wp-helper-functions.php';
    }

    /**
     * Setup function to be called after _each_ unit test. This is *always* required.
     */
    function tearDown()
    {
        Container::instance()->reset();
        \Mockery::close();
        \Patchwork\restoreAll();
    }
}

namespace Brain\Monkey\Functions {

    use Brain\Monkey\Container;
    use Brain\Monkey\Expectation\FunctionStubFactory;
    use Brain\Monkey\Name\FunctionName;

    /**
     * API entrypoint for plain functions stub.
     *
     * Factory method: receives the name of the function to mock and returns an instance of
     * FunctionStub.
     *
     * @param  string $function_name the name of the function to mock
     * @return \Brain\Monkey\Expectation\FunctionStub
     */
    function when($function_name)
    {
        return Container::instance()
                        ->functionStubFactory()
                        ->create(new FunctionName($function_name), FunctionStubFactory::SCOPE_STUB);
    }

    /**
     * API method to fast & simple create multiple functions stubs.
     *
     * It does not allow to add expectations.
     *
     * The function name to create stub for can be passed as array key or as array value (with no key).
     *
     * When the function name is in the key, the value can be:
     *   - a callable, in which case the function will be aliased to it
     *   - anything else, in which case a stub returning given value will be created for the function
     * 
     * When the function name is in the value, and no key is set, the behavior will change based on
     * the second param:
     *   - when 2nd param is `null` (default) the created stub will return the 1st param it will receive
     *   - when 2nd param is anything else the created stub will return it
     *
     * 
     * @param array      $functions
     * @param mixed|null $default_return
     */
    function stubs(array $functions, $default_return = null)
    {
        foreach ($functions as $key => $value) {

            list($function_name, $return_value) = is_numeric($key)
                ? [$value, $default_return]
                : [$key, $value];

            if (is_callable($return_value)) {
                when($function_name)->alias($return_value);
                continue;
            }

            $return_value === null
                ? when($function_name)->returnArg()
                : when($function_name)->justReturn($return_value);
        }
    }

    /**
     * API entrypoint for plain functions expectations.
     *
     * Returns a Mockery Expectation object, where is possible to set all the expectations, using
     * Mockery methods.
     *
     * @param string $function_name
     * @return \Brain\Monkey\Expectation\Expectation
     */
    function expect($function_name)
    {
        $name = new FunctionName($function_name);
        $expectation = Container::instance()
                                ->expectationFactory()
                                ->forFunctionExecuted($function_name);

        $factory = Container::instance()->functionStubFactory();
        if ( ! $factory->has($name)) {
            $factory->create($name, FunctionStubFactory::SCOPE_EXPECTATION)
                    ->redefineUsingExpectation($expectation);

        }

        return $expectation;
    }
}

namespace Brain\Monkey\Actions {

    use Brain\Monkey\Container;
    use Brain\Monkey\Hook;

    /**
     * API entrypoint for added action expectations.
     *
     * Takes the action name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $action
     * @return \Brain\Monkey\Expectation\Expectation
     */
    function expectAdded($action)
    {
        return Container::instance()
                        ->expectationFactory()
                        ->forActionAdded($action);
    }

    /**
     * API entrypoint for fired action expectations.
     *
     * Takes the action name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $action
     * @return \Brain\Monkey\Expectation\Expectation
     */
    function expectDone($action)
    {
        return Container::instance()
                        ->expectationFactory()
                        ->forActionDone($action);
    }

    /**
     * Utility method to check if any or specific callback has been added to given action.
     *
     * Brain Monkey version of `has_action` will alias here.
     *
     * @param string $action
     * @param null   $callback
     * @return bool
     */
    function has($action, $callback = null)
    {
        return Container::instance()
                        ->hookStorage()
                        ->isHookAdded(Hook\HookStorage::ACTIONS, $action, $callback);
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
        return Container::instance()
                        ->hookStorage()
                        ->isHookDone(Hook\HookStorage::ACTIONS, $action);
    }

    /**
     * Utility method to check if given action is currently being done.
     *
     * Brain Monkey version of `doing_action` will alias here.
     *
     * @param string $action
     * @return bool
     */
    function doing($action)
    {
        return Container::instance()
                        ->hookRunningStack()
                        ->has($action);
    }
}

namespace Brain\Monkey\Filters {

    use Brain\Monkey\Container;
    use Brain\Monkey\Hook;

    /**
     * API entrypoint for added filter expectations.
     *
     * Takes the filter name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $filter
     * @return \Brain\Monkey\Expectation\Expectation
     */
    function expectAdded($filter)
    {
        return Container::instance()
                        ->expectationFactory()
                        ->forFilterAdded($filter);
    }

    /**
     * API entrypoint for applied filter expectations.
     *
     * Takes the filter name and returns a Mockery Expectation object, where is possible to set all
     * the expectations, using Mockery methods.
     *
     * @param string $filter
     * @return \Brain\Monkey\Expectation\Expectation
     */
    function expectApplied($filter)
    {
        return Container::instance()
                        ->expectationFactory()
                        ->forFilterApplied($filter);
    }

    /**
     * Utility method to check if any or specific callback has been added to given filter.
     *
     * Brain Monkey version of `has_filter` will alias here.
     *
     * @param string $filter
     * @param null   $callback
     * @return bool
     */
    function has($filter, $callback = null)
    {
        return Container::instance()
                        ->hookStorage()
                        ->isHookAdded(Hook\HookStorage::FILTERS, $filter, $callback);
    }

    /**
     * Utility method to check if given filter as been applied.
     *
     * There's no WordPress function counter part for it.
     *
     * @param string $filter
     * @return int
     */
    function applied($filter)
    {
        return Container::instance()
                        ->hookStorage()
                        ->isHookDone(Hook\HookStorage::FILTERS, $filter);
    }

    /**
     * Utility method to check if given filter is currently being done.
     *
     * Brain Monkey version of `doing_filter` will alias here.
     *
     * @param string $filter
     * @return bool
     */
    function doing($filter)
    {
        return Container::instance()
                        ->hookRunningStack()
                        ->has($filter);
    }
}


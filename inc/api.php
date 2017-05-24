<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the BrainMonkey package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey {

    function setUp()
    {
        require_once dirname(__DIR__).'/inc/wp-hook-functions.php';
        require_once dirname(__DIR__).'/inc/wp-helper-functions.php';
    }

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


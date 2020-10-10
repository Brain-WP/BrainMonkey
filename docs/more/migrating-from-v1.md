# Migration from v1

## \[Updated\] Patchwork Version

Patchwork has been updated to version 2. This new version allows to redefine PHP core functions and not only custom defined functions. \(There are limitations, see [http://patchwork2.org/limitations/](http://patchwork2.org/limitations/)\).

This new Patchwork version seems to also fix an annoying issue with undesired Patchwork cache.

## \[Changed\] Setup Functions - BREAKING!

On version 1 of Brain Monkey there where 4 static methods dedicated to setup:

* `Brain\Monkey::setUp()` -&gt; before each test that use only functions redefinition \(no WP features\)
* `Brain\Monkey::tearDown()` -&gt; after each test that use only functions redefinition \(no WP features\)
* `Brain\Monkey::setUpWp()` -&gt; before each test that use functions redefinition and WP features
* `Brain\Monkey::tearDownWp()` -&gt; after each test that use functions redefinition and WP features

This has been simplified, in fact, **only two setup functions exists in Brain Monkey v2**:

* `Brain\Monkey\setUp()` -&gt; before each test that use functions redefinition and WP features
* `Brain\Monkey\tearDown()` -&gt; after each test, no matter if for functions redefinition or for also 

  WP features

Which means that for function redefinitions, only `Brain\Monkey\tearDown()` have to be called after each test, and nothing _before_ each test.

To also use WP features, `Brain\Monkey\setUp()` have also to called before each test.

## \[Changed\] New API - BREAKING!

Big part of Brain Monkey is acting as a "bridge" between Mockery an Patchwork, that is, make Mockery DSL for expectations available for functions and WordPress hooks.

To access the Mockery API, Brain Monkey v1 provided two different methods:

1. using static methods on the `Brain\Monkey` class
2. using static methods on one of the three feature-specific classes `Brain\Monkey\Functions`, 

   `Brain\Monkey\WP\Actions` or `Brain\Monkey\WP\Filters`

For example:

```php
// Brain Monkey v1 method one
Brain\Monkey::functions::expect('some_function');
Brain\Monkey::actions()->expectAdded('init');
Brain\Monkey::filters()->expectApplied('the_title');

// Brain Monkey v1 method two
Brain\Monkey\Functions::expect('some_function');
Brain\Monkey\WP\Actions::expectAdded('init');
Brain\Monkey\WP\Filters::expectApplied('the_title');
```

In Brain Monkey v2 there's only one method, that makes use of **functions**:

```php
// Brain Monkey v2
Brain\Monkey\Functions\expect('some_function');
Brain\Monkey\Actions\expectAdded('init');
Brain\Monkey\Filters\expectApplied('the_title');
```

### Renamed method for done actions

For WordPress filters, there were in Brain Monkey v1 two methods:

* `Filters::expectAdded()`
* `Filters::expectApplied()`

named after the WordPress functions `add_filter()` / `apply_filters()`

But for actions there were:

* `Actions::expectAdded()`
* `Actions::expectFired()`

`expectAdded()` pairs with `add_action()`, but `expectFired()` does not really pair with `do_action()`: this is why in Brain Monkey v2 **the method `expectFired()` has been replaced by the function `expectDone()`**.

So, in version 2 there are total of 5 entry-point **functions** to Mockery API:

* `Brain\Monkey\Functions\expect()`
* `Brain\Monkey\Actions\expectAdded()`
* `Brain\Monkey\Actions\expectDone()`
* `Brain\Monkey\Filters\expectAdded()`
* `Brain\Monkey\Filters\expectApplied()`

## \[Changed\] Default Expectations Behavior - BREAKING!

In Brain Monkey v1, expectation on the "times" an expected event happen was required.

```php
class MyClass {

    public function doSomething() {
      return true;
    }
}


class MyClassTest extends MyTestCase {

    // this test passes in Brain Monkey v1
    public function testSomething() {
        \Brain\Monkey\WP\Actions::expectAdded('init'); // this has pretty much no effect
        $class = new MyClass();
        self::assertTrue($class->doSomething());
    }
}
```

This **test passed in Brain Monkey v1**, because even if `Actions::expectAdded()` was used, the test does not fail unless something like `Actions::expectAdded('init')->once()` was used, which made the test pass only if `add_action( 'init' )` was called once.

The reason is that Mockery default behavior is to add a `->zeroOrMoreTimes()` as expectation on number of times a method is called, so when the expectation is called _zero times_, that's a valid outcome.

This was somehow confusing \(because reading `expectAdded` one could _expect_ the test to fail if that thing did not happened\), and also made tests unnecessarily verbose.

**Brain Monkey v2, set Mockery expectation default to `->atLeast()->once()`** so, for example, the test above fails in Brain Monkey v2 if `MyClass::doSomething()` does not call `add_action('init')` at least once.

## \[Changed\] Closure String Representation - BREAKING!

Brain Monkey allows to do some basic tests using `has_action()` / `has_filter()`, functions, to test if some portion of code have added some hooks.

A "special" syntax, was already added in Brain Monkey v1 to permit the checking for hooks added using object instances as part of the hook callback, without having any reference to those objects.

For example, assuming a function like:

```php
namespace A\Name\Space;

function test() {

  add_action('example_one', [new SomeClass(), 'aMethod']);

  add_action('example_two', function(array $foo) { /* ... */ });
}
```

could be tested with in Brain Monkey v1 with:

```php
// Brain Monkey v1:
test();
self::assertNotFalse(has_action('example_one', 'A\Name\Space\SomeClass->aMethod()')); // pass
self::assertNotFalse(has_action('example_two', 'function()')); // pass
```

The syntax for string representation of callbacks including objects is unchanged in Brain Monkey v2, however, **the syntax for closures string representation has been changed to allow more fine grained control**.

In fact, in Brain Monkey v1 _all_ the closures were represented as the string `"function()"`, in Brain Monkey v2 closure string representations also contain the parameters used in the closure signature:

```php
// Brain Monkey v2:
test();
self::assertNotFalse(has_action('example_one', 'A\Name\Space\SomeClass->aMethod()')); // pass
self::assertNotFalse(has_action('example_two', 'function()')); // fail!
self::assertNotFalse(has_action('example_two', 'function(array $foo)')); // pass!
```

The closure string representation _does_ take into account:

* name of the parameters
* parameters type hints \(works with PHP 7+ scalar type hints\)
* variadic arguments
* `static` closures VS normal closures

_does not_ take into account:

* PHP 7 return type declaration
* parameters defaults
* content of the closure

For example:

```php
namespace A\Name\Space;

$closure_1 = static function( array $foo, SomeClass $bar, int ...$ids ) : bool { /* */ }

$closure_2 = function( array $foo, SomeClass $bar, array $ids = [] ) : bool { /* */ }

// $closure_1 is represented as:
"static function ( array $foo, A\Name\Space\SomeClass $bar, int ...$ids )";

// $closure_2 is represented as:
"function ( array $foo, A\Name\Space\SomeClass $bar, array $ids )";
```

Note how type-hints using classes always have fully qualified names in string representation.

## \[Changed\] Relaxed `callable` check

In Brain Monkey v1 methods and functions that accept a `callable` like, for example, second argument to `add_action()` / `add_filter()`, checked the received argument to be an actual callable PHP entity, using `is_callable`:

```php
// this fail in Brain Monkey v1 if `SomeClass` was not available 
// or if SomeClass::aMethod would not be a valid method
add_action( 'foo', [ SomeClass::class, 'aMethod' ] );

// this fail in Brain Monkey v1 if `Some\Name\Space\aFunction` is not available
add_action( 'bar', 'Some\Name\Space\aFunction' );
```

For these reasons, it was often required to create a mock for unavailable classes or functions just to don't make Brain Monkey throw an exception, even if the mock was not used and not relevant for the test.

Brain Monkey v2 is less strict on checking for `callable` and it accepts anything that _looks like_ a callable.

Something like `[SomeClass::class, 'aMethod']` would be accepted even if `SomeClass` is not loaded at all, because _it looks like_ a callable. Same goes for `'Some\Name\Space\aFunction'`.

However, something like `[SomeClass::class, 'a-Method']` or `[SomeClass::class, 'aMethod', 1]` or even `Some\Name\Space\a Function` will throw an exception because method and function names can't contain hyphens or spaces and when a callback is made of an array, it must have exactly two arguments.

This more "relaxed" check allows to save creation of mocks that are not necessary for the logic of the test.

It worth noting that when doing something like `[SomeClass::class, 'aMethod']` **if** the class `SomeClass` is available, Brain Monkey checks it to have an accessible method named `aMethod`, and raise an exception if not, but will not do any check if the class is not available.

The same applies when object instances are used for callbacks, for example, using as callback argument `[$someObject, 'aMethod']`, the instance of `$someObject` is checked to have an accessible method named `aMethod`.

## \[Fixed\] `apply_filters` Default Behavior

The WordPress function `apply_filters()` is defined by Brain Monkey and it returns the first argument passed to it, just like WordPress:

```php
self::assertSame('Foo', apply_filters('a_filter', 'Foo', 'Bar')); // pass!
```

In Brain Monkey v1 this was true _unless_ some expectation was added to the applied filter:

```php
Brain\Monkey\WP\Filters::expectApplied('a_filter');

self::assertSame('Foo', apply_filters('a_filter', 'Foo', 'Bar')); // fails in v1
```

**The test above fails in Brain Monkey v1**. The reason is that even if the expectation in first line is validated, it breaks the default `apply_filters` behavior, requiring the return value to be added to expectation to make the test pass again.

For example, the following test used to pass in Brain Monkey v1:

```php
Brain\Monkey\WP\Filters::expectApplied('a_filter')->andReturn('Foo');

self::assertSame('Foo', apply_filters('a_filter', 'Foo', 'Bar')); // pass
```

**In Brain Monkey v2 this is not necessary anymore.**

Calling `expectApplied` on applied filters does **not** break the default behavior of `apply_filters` behavior, if no return expectations are added.

The following test **passes in Brain Monkey v2**:

```php
Brain\Monkey\Filters\expectApplied('a_filter')->once()->with('Foo', 'Bar');

self::assertSame('Foo', apply_filters('a_filter', 'Foo', 'Bar')); // pass in v2!
```

Please note that if any return expectation is added for a filter, return expectations must be added for all the set of arguments the filter might receive.

For example:

```php
Brain\Monkey\Filters\expectApplied('a_filter')->once()->with('Foo')->andReturn('Foo!');
Brain\Monkey\Filters\expectApplied('a_filter')->once()->with('Bar');

self::assertSame('Foo!', apply_filters('a_filter', 'Foo')); // pass
self::assertSame('Bar', apply_filters('a_filter', 'Bar')); // fail!
```

The second assertion fails because since we added a return expectation for the filter "'a_filter'" we need to add return expectation for \_all_ the possible arguments.

This task is easier in Brain Monkey v2 thanks to the introduction of `andReturnFirstArg()` expectation method \(more on this below\).

For example:

```php
Brain\Monkey\Filters\expectApplied('a_filter')->once()->with('Foo')->andReturn('Foo!');
Brain\Monkey\Filters\expectApplied('a_filter')->zeroOrMoreTimes()->withAnyArgs()->andReturnFirstArg();

self::assertSame('Foo', apply_filters('a_filter', 'Foo', 'Bar')); // pass
self::assertSame('Bar', apply_filters('a_filter', 'Bar')); // pass!
```

`andReturnFirstArg()` used in combination with Mockery methods `zeroOrMoreTimes()->withAnyArgs()` allows to create a "catch all" behavior for filters when a return expectation has been added, without having to create specific expectations for each of the possible arguments a filter might receive.

Of course, adding specific expectations for each of the possible arguments a filter might receive is still possible.

## \[Added\] Utility Functions Stubs

There are WordPress functions that are often used in WordPress plugins or themes that are pretty much _logicless_, but still they need to be mocked in tests if WordPress is not available.

Brain Monkey v2 now ships stubs for those functions, so it is not necessary to mock them anymore, they are:

* `__return_true`
* `__return_false`
* `__return_null`
* `__return_empty_array`
* `__return_empty_string`
* `__return_zero`
* `trailingslashit`
* `untrailingslashit`

Those functions do exactly what they are expected to do, even if WordPress is not loaded: some functions mocking is now saved.

Of course, their behavior can still be mocked, e.g. to make a test fail on purpose.

## \[Added\] Support for `doing_action()` and `doing_filter()`

When adding expectation on returning value of filters, or when using `whenHappen` to respond to actions, inside the expectation callback, the function `current_filter()` in Brain Monkey v1 used to correctly resolve to the action / filter being executed.

The functions `doing_action()` and `doing_filter()` didn't work: they were not provided at all with Brain Monkey v1 and required to be mocked "manually" .

In Brain Monkey v2 those two functions are provided as well, and correctly return true or false when used inside the callbacks used to respond to hooks.

## \[Added\] Method `andReturnFirstArg()`

When adding expectations on returning value of applied filters or functions, it is now possible to use `andReturnFirstArg()` to make the Mockery expectations return first argument received.

```php
// Brain\Monkey v2:
Brain\Monkey\Functions\expect('foo')->andReturnFirstArg();
Brain\Monkey\Filters\expectApplied('the_title')->andReturnFirstArg();


// Brain\Monkey v1:
Brain\Monkey\Functions\expect('foo')->andReturnUsing(function($arg) {
  return $arg;
});

Brain\Monkey\Filters\expectApplied('the_title')->andReturnUsing(function($arg) {
  return $arg;
});
```

## \[Added\] Method `andAlsoExpectIt()`

In Mockery, when creating expectations for multiple methods of same class, the method `getMock()` allows to do it without leaving "fluent interface chain", for example:

```php
Mockery\mock(SomeClass::class)
  ->shouldReceive('exclamation')->with('Foo')->once()->andReturn('Foo!')
  ->getMock()
  ->shouldReceive('question')->with('Bar')->once()->andReturn('Bar?')
  ->getMock()
  ->shouldReceive('invert')->with('Baz')->once()->andReturn('zaB')
```

The method `getMock()` is **not** available for Brain Monkey expectations.

For this reason has been introduced `andAlsoExpectIt()`:

```php
Brain\Monkey\Filters\expectApplied('some_filter')
  ->once()->with('Hello')->andReturn('Hello!')
  ->andAlsoExpectIt()
  ->atLeast()->twice()->with('Hi')->andReturn('Hi!')
  ->andAlsoExpectIt()
  ->zeroOrMoreTimes()->withAnyArgs()->andReturnFirstArg();
```

Of course, it also works in other kind of expectations, like for functions or for actions added or done.

## \[Added\] New Exceptions Classes

In Brain Monkey v1, when exceptions were thrown, PHP core exception classes were used, like `\RuntimeException` or `\InvalidArgumentException`, and so on.

In Brain Monkey v2, different custom exceptions classes have been added, to make very easy to catch any error thrown by Brain Monkey.

Now, in fact, every exception thrown by Brain Monkey is of a custom type, and there's a hierarchy of exceptions classes for a total of 16 exception classes, all inheriting \(one or more levels deep\) the "base" exception class that is `Brain\Monkey\Exception`.


<!--
currentMenu: "wphooksadded"
currentSection: "WordPress"
title: "Testing Added Hooks"
-->
# Testing Added Hooks

With Brain Monkey there are two ways to test some hook have been added, and with which arguments.

First method (easier) makes use of WordPress functions, the second (more powerful) makes use of Brain Monkey (Mockery) expectation DSL.

## Testing framework agnostic

Brain Monkey can be used with any testing framework.
Examples in this page will use PHPUnit, but the concepts are applicable to any testing framework.

Also note that test classes in this page extends the class `MyTestCase` that is assumed very similar to the one coded in the *WordPress / Setup* docs section.


## Testing with WordPress functions: `has_action()` and `has_filter()`

When Brain Monkey is loaded for tests it registers all the functions of WordPress plugin API (see *WordPress / WordPress Testing Tools*).
Among them there are `has_action()` and `has_filter()` that, just like *real* WordPress functions can be used to test if some hook (action or filter)
has been added, and also verify the arguments.

Let's assume the code to be tested is:

```php
class MyClass
{
    function addHooks()
    {
       add_action('init', array('MyClass', 'init'), 20);
       add_filter('the_title', array('MyClass', 'the_title'), 99);
    }
}
```

in Brain Monkey, just like in real WordPress code, you can test hooks are added using WordPress functions:

```php
class MyClassTest extends MyTestCase
{
    function testAddHooksActuallyAddsHooks()
    {
        $class = new MyClass();
        $class->addHooks();
        $this->assertTrue( has_action('init', array('MyClass', 'init'), 20) );
        $this->assertTrue( has_filter('the_title', array('MyClass', 'the_title'), 99) );
    }
}
```

Nice thing of this approach is that you don't need to remember Brain Monkey classes and methods names, you can just use function you, as a WordPress developer, are already used to use.

There's more.

A problem of WordPress hooks is that when dynamic object methods or anonymous functions are used, identify them is not easy. It's pretty hard, to be honest.
But Brain Monkey is not WordPress, and it makes these sort of things very easy. Let's assume the code to test is:

```php
class MyClass
{
    function addHooks()
    {
       add_action('init', array($this, 'init'), 20);
       add_filter('the_title', function($title) {
          return $title;
       }, 99);
    }
}
```

Using real WordPress functions, to check hooks added like in code above is pretty hard, because we don't have access to `$this` outside of the class, nor to the anonymous function.

But Brain Monkey version of `has_action` and `has_filter` allow to check this cases with a very intuitive syntax:

```php
class MyClassTest extends MyTestCase
{
    function testAddHooksActuallyAddsHooks()
    {
        $class = new MyClass();
        $class->addHooks();

        $this->assertTrue( has_action('init', 'MyClass->init()', 20) );
        $this->assertTrue( has_filter('the_title', 'function()', 99) );
    }
}
```

So we have identified
 - a dynamic method by using the class name, followed by `->` and the method name followed by parenthesis.
 - an anonymous function using `'function()''`

Moreover
 - a static method can be identified by the class name followed by `::` and the method name followed by parenthesis, e.g. `'MyClass::init()'`
 - an invokable object (a class with a `__invoke()` method) can be identified by the class name followed by parenthesis, e.g. `'MyClass()'`


## Testing with expectations

Even if the doing tests using WordPress native functions is pretty easy, there are cases in which is not enough powerful,
or the expectation methods are just more convenient.

Moreover, Brain Monkey functions always try to mimic WordPress real functions behavior and so a call to `remove_action` or `remove_filter` can make
impossible to test some code using `has_action` and `has_filter`, because hooks are actually removed.

The solution is to use expectations, provided in Brain Monkey by Mockery. Assuming the code to test is last version above of `MyClass`, we can test it like so:

```php
use Brain\Monkey\WP\Actions;
use Brain\Monkey\WP\Filters;

class MyClassTest extends MyTestCase
{
    function testAddHooksActuallyAddsHooks()
    {
        Actions::expectAdded('init')->once();

        Filters::expectAdded('the_title')
            ->atLeast()
            ->once()
            ->with(Mockery\type('Closure'));

        // let's use the code that have to satisfy our expectations
        $class = new MyClass();
        $class->addHooks();
    }
}
```

This is just an example, but Mockery expectations are a very powerful testing mechanism.

To know more, read [Mockery documentation](http://docs.mockery.io/en/latest/), and have a look to *PHP Functions* doc section
to see how it is used seamlessly in Brain Monkey.

## Expectations on times are required

`expectAdded()` returns a Mockery expectation object, and by default it doesn't throw any error if a created expectation object is not used, unless a specific
times expectation is set.

It means that if you use `Actions::expectAdded('init');` and nothing more, the test will pass even if `add_action('init')` is never run by code under test.

To be sure hooks were added you need to set times expectation. Mockery has a powerful and intuitive syntax for the scope, just a few examples:

```php
Actions::expectAdded('init')->once();
Actions::expectAdded('init')->twice();
Actions::expectAdded('init')->times(3);
Actions::expectAdded('init')->atLeast()->twice();
Actions::expectAdded('init')->atMost()->times(3);
Actions::expectAdded('init')->between(2, 4);
Actions::expectAdded('init')->never();
```

See Mockery documentation and  *PHP Functions* doc section for more examples and explanation (if ever needed...).


## Just a couple of things...

 - expectations must be set *before* the code to be tested runs: they are called "expectations" for a reason
 - argument validation done using `with()`, validates hook arguments, not function arguments, it means what is passed to `add_action()` or `add_filter()` **excluding** hook name itself


## Don't set expectations on return values for added hooks

Maybe you already know that `add_action()` and `add_filter()` always return `true`.

As already said, Brain Monkey always tries to make WordPress functions behave how they do in real WordPress code, for this reason Brain Monkey
version of those functions returns `true` as well.

But if you read *PHP Functions* doc section or Mockery documentation you probably noticed a `andReturn` method that allows to force an expectation to
return a given value.

Once `expectAdded()` method works with Mockery expectations, you may be tempted to use it... if you do that **an exception will be thrown**.

```php
// this expectation will thrown an error!
Filters::expectAdded('the_title')->once()->andReturn(false);
```

Reason is that if Brain Monkey had allowed a *mocked* returning value for `add_action` and `add_filter` that had been in contrast with real
WordPress code, with disastrous effects on tests.

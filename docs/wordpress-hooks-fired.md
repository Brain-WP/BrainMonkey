<!--
currentMenu: "wphooksfired"
currentSection: "WordPress"
title: "Testing Fired Hooks"
-->

# Testing Fired Hooks

## Testing framework agnostic

Brain Monkey can be used with any testing framework.
Examples in this page will use PHPUnit, but the concepts are applicable to any testing framework.

Also note that test classes in this page extends the class `MyTestCase` that is assumed very similar to the one coded in the *WordPress / Setup* docs section.


## Simple tests with `did_action()` and `filters()->applied()`

To check hooks has been fired, the only available WordPress function is `did_action()` (doesn't exist any `did_filter()` or `applied_filter()`).

To overcome the missing counter part of `did_action()` for filters, Brain Monkey has a method accessible via `Brain\Monkey::filters()->applied()`
that does what you might expect.

Assuming a class like the following:

```php
class MyClass
{
    function fireHooks()
    {
       do_action('my_action', $this);

       return apply_filters('my_filter', 'Filter applied', $this);
    }
}
```

It can be tested using:

```php
use Brain\Monkey;

class MyClassTest extends MyTestCase
{
    function testFireHooksActuallyFiresHooks()
    {
        $class = new MyClass();
        $class->fireHooks();

        $this->assertSame( 1, did_action('my_action') );
        $this->assertTrue( Monkey::filters()->applied('my_filter') > 0 );
    }
}
```

As you can guess from test code above, `did_action()` and `filters()->applied()` return the number of times an action or a filter
has been triggered, just like `did_action()` does in WordPress, but there's no way to use them to check which arguments were passed to the fired hook.

So, `did_action()` and `filters()->applied()` are fine for simple tests, mostly because using them you don't need to recall Brain Monkey methods,
but they are not very powerful: arguments checking and, above all, the ability to respond to fired hooks are pivotal tasks to proper test WordPress code.

In Brain Monkey those tasks can be done testing fired hooks with expectations.


## Test fired hooks with expectations

A powerful testing mechanism for fired hooks is provided by Brain Monkey thanks to Mockery expectations.

The entry points to use it are the `Actions::expectFired()` and `Filters::expectApplied()` methods.

As usual, below there a just a couple of examples, for the full story see [Mockery docs](http://docs.mockery.io/en/latest/reference/expectations.html).

Assuming the `MyClass` above in this page, it can be tested with:


```php
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

class MyClassTest extends MyTestCase
{
    function testFireHooksActuallyFiresHooks()
    {
        Actions::expectFired('my_action')
            ->once()
            ->with(Mockery::type('MyClass'));

        Filters::expectApplied('my_filter')
            ->once()
            ->with('Filter applied', Mockery::type('MyClass'));

        $class = new MyClass();
        $class->fireHooks();
    }
}
```

## Expectations on times are required

`expectFired()` and `expectApplied()` return a Mockery expectation object, and by default it does not throw any error if a created expectation object is not used, unless a specific
times expectation is set.

It means that if you use `Actions::expectFired('action');` and nothing more, the test will pass even if `do_action('action')` is never run by code under test.

To be sure hooks were fired you need to set times expectation. Mockery has a powerful and intuitive syntax for the scope, just a few examples:

```php
Actions::expectFired('action')->once();
Filters::expectApplied('filter')->twice();
Actions::expectFired('action')->times(3);
Filters::expectApplied('filter')->atLeast()->twice();
Actions::expectFired('action')->atMost()->times(3);
Filters::expectApplied('filter')->between(2, 4);
Actions::expectFired('action')->never();
```

See Mockery documentation and  *PHP Functions* doc section for more examples and explanation (if ever needed...).


## Just a couple of things...

 - expectations must be set *before* the code to be tested runs: they are called "expectations" for a reason
 - argument validation done using `with()`, validates hook arguments, not function arguments, it means what is passed to `do_action` or `apply_filters` **excluding** hook name itself


## Respond to filters

Yet again, Brain Monkey, when possible, tries to make WordPress functions it redefines behave in the same way of *real* WordPress functions.

Brain Monkey `apply_filters` by default returns the first argument passed to it, just like WordPress function does when no callback is added to the filter.

However, sometimes in tests is required that a filter would return something different.

Luckily, Mockery provide `andReturn()` and `andReturnUsing()` expectation methods that can be used to make a filter return whatever.

```php
use Brain\Monkey\Filters;

class MyClassTest extends MyTestCase
{
    function testFireHooksReturnValue()
    {
        Filters::expectApplied('my_filter')
            ->once()
            ->with('Filter applied', Mockery::type('MyClass'))
            ->andReturn('Brain Monkey rocks!');

        $class = new MyClass();

        $this->assertSame('Brain Monkey rocks!', $class->fireHooks());
    }
}
```

See [Mockery docs](http://docs.mockery.io/en/latest/reference/expectations.html) for more information.


## Respond to actions

To return a value from a filter is routine, not so for actions.

In fact, `do_action()` always returns `null` so, if Brain Monkey would allow a *mocked* returning value for `do_action()` expectations,
it would be in contrast with real WordPress code, with disastrous effects on tests.

So, don't try to use `andReturn()` and `andReturnUsing()` with `Actions::expectFired()` because it will throw an exception.

However, sometimes one may be in the need do *something* when code calls `do_action()`, like WordPress actually does.
This is the reason Brain Monkey introduces `whenHappen()` method for action expectations that receives a callback to be ran when an action is fired.

Let's assume a class like the following:

```php
class MyClass
{
    public $post;

    function setPost()
    {
        global $post;
        $this->post = $post;

        do_action('my_class_set_post', $this);

        return $post;
    }
}
```

It is possible write a test like this:

```php
use Brain\Monkey\Actions;

class MyClassTest extends MyTestCase
{
    function testFireHooksReturnValue()
    {
        Action::expectFired('my_class_set_post')
            ->once()
            ->with(Mockery::type('MyClass'))
            ->whenHappen(function($my_class) {
                $my_class->post = (object) ['post_title' => 'Mocked!'];
            });

        $class = new MyClass();
        $class->setPost();

        $this->assertSame( 'Mocked!', $class->post->post_title );
    }
}
```

Surely, mocking returned value for filters is more common and actually useful, but in case you need this feature, Brain Monkey has it.


## Resolving `current_filter()`

When WordPress is not performing an hook, `current_filter()` returns `false`. And so does the Brain Monkey version of that function.

Now I want to surprise you: `current_filter()` correctly resolves to the correct hook during the execution of any callback added to respond to hooks.

Let's assume a class like the following:

```php
class MyClass
{
    function getValues()
    {
        $title = apply_filters('my_class_title', '');
        $content = apply_filters('my_class_content', '');

        return [$title, $content];
    }
}
```

It is possible write a test like this:

```php
use Brain\Monkey\Filters;

class MyClassTest extends MyTestCase
{
    function testGetValues()
    {
        $callback = function() {
            return current_filter() === 'my_class_title' ? 'Title' : 'Content';
        };

        Filters::expectApplied('my_class_title')->once()->andReturnUsing($callback);
        Filters::expectApplied('my_class_content')->once()->andReturnUsing($callback);

        $class = new MyClass();

        $this->assertSame( ['Title', 'Content'], $class->getValues() );
    }
}
```

Like a magic, `current_filter()` returns the right hook just like it does in WordPress. Note this will also work with any callback passed to `whenHappen()`.

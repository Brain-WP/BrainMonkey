# Test done hooks

## Testing framework agnostic

Brain Monkey can be used with any testing framework. Examples in this page will use PHPUnit, but the concepts are applicable to any testing framework.

Also note that test classes in this page extends the class `MyTestCase` that is assumed very similar to the one coded in the _WordPress / Setup_ docs section.

## Simple tests with `did_action()` and `Filters\applied()`

To check hooks have been fired, the only available WordPress function is `did_action()`, it doesn't exist any `did_filter()` or `applied_filter()`.

To overcome the missing counter part of `did_action()` for filters, Brain Monkey has a method accessible via `Brain\Monkey\Filters\applied()` that does what you might expect.

Assuming a class like the following:

```php
class MyClass {

    function fireHooks() {

       do_action('my_action', $this);

       return apply_filters('my_filter', 'Filter applied', $this);
    }
}
```

It can be tested using:

```php
use Brain\Monkey\Filters;

class MyClassTest extends MyTestCase
{
    function testFireHooksActuallyFiresHooks()
    {
        ( new MyClass() )->fireHooks();

        $this->assertSame( 1, did_action('my_action') );
        $this->assertTrue( Filters\applied('my_filter') > 0 );
    }
}
```

As you can guess from test code above, `did_action()` and `Filters\applied()` return the number of times an action or a filter has been triggered, just like `did_action()` does in WordPress, but there's no way to use them to check which arguments were passed to the fired hook.

So, `did_action()` and `Filters\applied()` are fine for simple tests, mostly because using them you don't need to recall Brain Monkey methods, but they are not very powerful: arguments checking and, above all, the ability to respond to fired hooks are pivotal tasks to proper test WordPress code.

In Brain Monkey those tasks can be done testing fired hooks with expectations.

## Test fired hooks with expectations

A powerful testing mechanism for fired hooks is provided by Brain Monkey thanks to Mockery expectations.

The entry points to use it are the `Actions\expectDone()` and `Filters\expectApplied()` functions.

As usual, below there a just a couple of examples, for the full story see [Mockery docs](http://docs.mockery.io/en/latest/reference/expectations.html).

Assuming the `MyClass` above in this page, it can be tested with:

```php
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

class MyClassTest extends MyTestCase
{
    function testFireHooksActuallyFiresHooks()
    {
        Actions\expectDone('my_action')
            ->once()
            ->with(Mockery::type(MyClass::class));

        Filters\expectApplied('my_filter')
            ->once()
            ->with('Filter applied', Mockery::type(MyClass::class));

        ( new MyClass() )->fireHooks();
    }
}
```

## Just a couple of things...

* expectations must be set _before_ the code to be tested runs: they are called "expectations" for a reason
* argument validation done using `with()`, validates hook arguments, not function arguments, it means what is passed to `do_action` or `apply_filters` **excluding** hook name itself

## Respond to filters

Yet again, Brain Monkey, when possible, tries to make WordPress functions it redefines behave in the same way of _real_ WordPress functions.

Brain Monkey `apply_filters` by default returns the first argument passed to it, just like WordPress function does when no callback is added to the filter.

However, sometimes in tests is required that a filter returns something different.

Luckily, Mockery provides `andReturn()` and `andReturnUsing()` expectation methods that can be used to make a filter return anything.

```php
use Brain\Monkey\Filters;

class MyClassTest extends MyTestCase {

    function testFireHooksReturnValue() {

        Filters\expectApplied('my_filter')
            ->once()
            ->with('Filter applied', Mockery::type(MyClass::class))
            ->andReturn('Brain Monkey rocks!');

        $class = new MyClass();

        $this->assertSame('Brain Monkey rocks!', $class->fireHooks());
    }
}
```

See [Mockery docs](http://docs.mockery.io/en/latest/reference/expectations.html) for more information.

Brain Monkey also provides the helper `andReturnFirstArg()` that can be used to make a filter expectation behave like WordPress does: return first argument received:

```php
Filters\expectApplied('my_filter')->once()->andReturnFirstArg();

self::assertSame( 'foo', apply_filters( 'my_filter', 'foo', 'bar' ) );
```

Note that in the example right above, the expectation would not be necessary; in fact, the assertion verify either way because it is the default behavior of WordPress and Brain Monkey.

But this is very helpful what we want to set expectations and returned values for filters based on some received arguments, for example:

```php
Filters\expectApplied('my_filter')->once()->with('foo')->andReturnFirstArg();
Filters\expectApplied('my_filter')->once()->with('bar')->andReturn('This time bar!');

self::assertSame( 'Foo', apply_filters( 'my_filter', 'Foo' ) );
self::assertSame( 'This time bar!', apply_filters( 'my_filter', 'Bar' ) );
```

Finally note that when setting different expectations for same filter, but for different received arguments, an expectation is required to be set for **all** the arguments that the filter is going to receive. For example this will fail:

```php
Filters\expectApplied('my_filter')->once()->with('foo')->andReturnFirstArg();
Filters\expectApplied('my_filter')->once()->with('bar')->andReturn('This time bar!');

self::assertSame( 'Foo', apply_filters( 'my_filter', 'Foo' ) );
self::assertSame( 'This time bar!', apply_filters( 'my_filter', 'Bar' ) );
self::assertSame( 'Meh!', apply_filters( 'my_filter', 'Meh!' ) );
```

The reason for failing is that there's no expectation set when the filter receives `"Meh!"`.

In such case, `andReturnFirstArg()` comes useful again, to set a "catch all" expectation:

```php
Filters\expectApplied('my_filter')->once()->with('bar')->andReturn('This time bar!');
// Catch all the other cases with the default:
Filters\expectApplied('my_filter')->once()->withAnyargs()->andReturnFirstArg();

// All the following passes!
self::assertSame( 'Foo', apply_filters( 'my_filter', 'Foo' ) );
self::assertSame( 'This time bar!', apply_filters( 'my_filter', 'Bar' ) );
self::assertSame( 'Meh!', apply_filters( 'my_filter', 'Meh!' ) );
```

## Respond to actions

To return a value from a filter is routine, not so for actions.

In fact, `do_action()` always returns `null` so, if Brain Monkey would allow a _mocked_ returning value for `do_action()` expectations, it would be in contrast with real WordPress code, with disastrous effects on tests.

So, don't try to use neither `andReturn()` or `andReturnUsing()` with `Actions\expectDone()` because it will throw an exception.

However, sometimes one may be in the need do _something_ when code calls `do_action()`, like WordPress actually does.

This is the reason Brain Monkey introduces `whenHappen()` method for action expectations. The method takes a callback to be ran when an action is fired.

Let's assume a class like the following:

```php
class MyClass {

    public $post;

    function setPost() {

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

class MyClassTest extends MyTestCase {

    function testFireHooksReturnValue() {

        Action\expectDone('my_class_set_post')
            ->with(Mockery::type(MyClass::class))
            ->whenHappen(function($my_class) {
                $my_class->post = (object) ['post_title' => 'Mocked!'];
            });

        ( new MyClass() )->setPost();

        $this->assertSame( 'Mocked!', $class->post->post_title );
    }
}
```

## Resolving `current_filter()`, `doing_action` and `doing_filter()`

When WordPress is not performing an hook, `current_filter()` returns `false`.

And so does the Brain Monkey version of that function.

Now I want to surprise you: `current_filter()` correctly resolves to the correct hook during the execution of any callback added to respond to hooks.

Let's assume a class like the following:

```php
class MyClass {

    function getValues() {

        $title   = apply_filters('my_class_title', '');
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

        Filters\expectApplied('my_class_title')->once()->andReturnUsing($callback);
        Filters\expectApplied('my_class_content')->once()->andReturnUsing($callback);

        $class = new MyClass();

        $this->assertSame(['Title', 'Content'], $class->getValues());
    }
}
```

Like magic, inside our callback, `current_filter()` returns the right hook just like it does in WordPress. Note this will also work with any callback passed to `whenHappen()`.

Surprised? There's more: inside callbacks used to respond to actions and filters, `doing_action()` and `doing_filter()` works as well!

Assuming a class like the following:

```php
class MyClass {

    function doStuff() {
        do_action( 'trigger_an_hook' );
    }
}
```

It is possible to write a test like this:

```php
use Brain\Monkey\Actions;

class MyClassTest extends MyTestCase {

    function testDoStuff() {

        // 'an_hook' action is done below in the "whenHappen" callback
        Actions\expectDone( 'an_hook' )->once()->whenHappen(function() {

           self::assertTrue( doing_action('an_hook') );

           // doing_action() also resolves the "parent" hook like it was WordPress!
           self::assertTrue( doing_action('trigger_an_hook') );
        });

        Actions\expectDone('trigger_an_hook')->once()->whenHappen(function() {
           if( current_filter() === 'trigger_an_hook' ) {
                 do_action('an_hook');
           }
        });
    }
}
```


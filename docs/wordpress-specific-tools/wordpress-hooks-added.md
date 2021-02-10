# Test added hooks

With Brain Monkey there are two ways to test some hook have been added, and with which arguments.

First method \(easier\) makes use of WordPress functions, the second \(more powerful\) makes use of Brain Monkey \(Mockery\) expectation DSL.

## Testing framework agnostic

Brain Monkey can be used with any testing framework. Examples in this page will use PHPUnit, but the concepts are applicable to any testing framework.

Also note that test classes in this page extends the class `MyTestCase` that is assumed very similar to the one coded in the _WordPress / Setup_ docs section.

## Testing with WordPress functions: `has_action()` and `has_filter()`

When Brain Monkey is loaded for tests it registers all the functions of WordPress plugin API \(see _WordPress / WordPress Testing Tools_\). Among them there are `has_action()` and `has_filter()` that, just like _real_ WordPress functions can be used to test if some hook \(action or filter\) has been added, and also verify the arguments.

Let's assume the code to be tested is:

```php
namespace Some\Name\Space;

class MyClass {

    public function addHooks() {

       add_action('init', [__CLASS__, 'init'], 20);
       add_filter('the_title', [__CLASS__, 'the_title'], 99);
    }
}
```

in Brain Monkey, just like in real WordPress code, you can test hooks are added using WordPress functions:

```php
use Some\Name\Space\MyClass;

class MyClassTest extends MyTestCase {

  public function testAddHooksActuallyAddsHooks() {

        ( new MyClass() )->addHooks();
        self::assertNotFalse( has_action('init', [ MyClass::class, 'init' ]) );
        self::assertNotFalse( has_filter('the_title', [ MyClass::class, 'the_title' ] ) );
    }
}
```

Nice thing of this approach is that you don't need to remember Brain Monkey classes and methods names, you can just use functions you, as a WordPress developer, are already used to use.

There's more.

A problem of WordPress hooks is that when dynamic object methods or anonymous functions are used, identify them is not easy. It's pretty hard, to be honest.

But Brain Monkey is not WordPress, and it makes these sort of things very easy. Let's assume the code to test is:

```php
namespace Some\Name\Space; 

class MyClass {

    public function init() {
      /* ... */
    }

    public function addHooks() {
       add_action('init', [ $this, 'init' ], 20);
    }
}
```

Using real WordPress functions, to check hooks added like in code above is pretty hard, because we don't have access to `$this` outside of the class.

But Brain Monkey version of `has_action` and `has_filter` allow to check this cases with a very intuitive syntax:

```php
class MyClassTest extends MyTestCase
{
    public function testAddHooksActuallyAddsHooks()
    {
        $class = new \Some\Name\Space\MyClass\MyClass();
        $class->addHooks();

        self::assertSame( 20, has_action( 'init', 'Some\Name\Space\MyClass->init()' ) );
    }
}
```

So we have identified a dynamic method by using the class name, followed by `->` and the method name followed by parenthesis.

Moreover

* a static method can be identified by the class name followed by `::` and the method name followed by parenthesis, e.g. `'Some\Name\Space\MyClass::init()'`
* an invokable object \(a class with a `__invoke()` method\) can be identified by the class name followed by parenthesis, e.g. `'Some\Name\Space\MyClass()'`

Note that fully qualified names of classes are used and namespace.

### Identify Closures

One tricky thing when working with hooks and closures in WordPress is that they are hard to identify, for example to remove or even to check via `has_action()` / `has_filter()` if a specific closure has been added to an hook.

Brain Monkey makes this a bit easier thanks to a sort of "serialization" of closures: a closure can be identified by a string very similar to the PHP code used to define the closure. Hopefully, an example will make it more clear.

Assuming a code like:

```php
namespace Some\Name\Space; 

class MyClass {

  public function addHooks() {

       add_filter('the_title', function($title) {
          return $title;
       }, 99);
    }
}
```

It could be tested with:

```php
class MyClassTest extends MyTestCase
{
    public function testAddHooksActuallyAddsHooks()
    {
        $class = new \Some\Name\Space\MyClass();
        $class->addHooks();

        self::assertNotFalse( has_filter('the_title', 'function ($title)' ) );
    }
}
```

It also works with type-hints and variadic arguments. E.g. a closure like:

```php
namespace Foo\Bar;

function( array $foo, Baz $baz, Bar ...$bar) {
  // ....
}
```

could be identified like this:

```php
'function ( array $foo, Foo\Bar\Baz $baz, Foo\Bar\Bar ...$bar )';
```

Just note how classes used in type-hints were using _relative_ namespace on declaration, always need the fully qualified name in the closure string representation.

PHP 7+ scalar type hints are perfectly supported.

The serialization also recognizes `static` closures. Following closure:

```php
static function( int $foo, Bar ...$bar ) {
  // ....
}
```

could be identified like this:

```php
'static function ( int $foo, Bar ...$bar )';
```

Things that are **not** took into account during serialization:

* default values for arguments
* PHP 7+ return type declarations

For example **all** following closures:

```php
function( array $foo, $bar ) {
  // ....
}

function( array $foo = [], $bar = null ) {
  // ....
}

function( array $foo, $bar ) : array {
  // ....
}

function( array $foo, $bar = null ) : array {
  // ....
}
```

are serialized into :

```php
'function ( array $foo, $bar )';
```

## Testing with expectations

Even if the doing tests using WordPress native functions is pretty easy, there are cases in which is not enough powerful, or the expectation methods are just more convenient.

Moreover, Brain Monkey functions always try to mimic WordPress real functions behavior and so a call to `remove_action` or `remove_filter` can make impossible to test some code using `has_action` and `has_filter`, because hooks are actually removed.

The solution is to use expectations, provided in Brain Monkey by Mockery.

Assuming the class to test is:

```php
namespace Some\Name\Space; 

class MyClass {

  public function addHooks() {

       add_action('init', [$this, 'init']);

       add_filter('the_title', function($title) {
          return $title;
       }, 99);
    }
}
```

it can be tested like so:

```php
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

class MyClassTest extends MyTestCase
{
    function testAddHooksActuallyAddsHooks()
    {
        Actions\expectAdded('init');

        Filters\expectAdded('the_title')->with(\Mockery::type('Closure'));

        // let's use the code that have to satisfy our expectations
       ( new \Some\Name\Space\MyClass() )->addHooks();
    }
}
```

This is just an example, but Mockery expectations are a very powerful testing mechanism.

To know more, read [Mockery documentation](http://docs.mockery.io/en/latest/), and have a look to _PHP Functions_ doc section to see how it is used seamlessly in Brain Monkey.

## Just a couple of things...

* expectations must be set _before_ the code to be tested runs: they are called "expectations" for a reason;
* argument validation done using `with()`, validates hook arguments, not function arguments, it means what is passed to `add_action()` or `add_filter()` **excluding** hook name itself.
* If you are errors related to `Call to undefined function add_action()` it could have to do with how you are loading your plugin file in the bootstrap.php file. See [some tips for procedural/OOP setup](https://github.com/Brain-WP/BrainMonkey/issues/90#issuecomment-745148097).

## Don't set expectations on return values for added hooks

Maybe you already know that `add_action()` and `add_filter()` always return `true`.

As already said, Brain Monkey always tries to make WordPress functions behave how they do in real WordPress code, for this reason Brain Monkey version of those functions returns `true` as well.

But if you read _PHP Functions_ doc section or Mockery documentation you probably noticed a `andReturn` method that allows to force an expectation to return a given value.

Once `expectAdded()` method works with Mockery expectations, you may be tempted to use it... if you do that **an exception will be thrown**.

```php
// this expectation will thrown an error!
Filters\expectAdded('the_title')->once()->andReturn(false);
```

Reason is that if Brain Monkey had allowed a _mocked_ returning value for `add_action` and `add_filter` that had been in contrast with real WordPress code, with disastrous effects on tests.


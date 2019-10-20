# Setup for functions testing

## Testing framework agnostic

Brain Monkey can be used with any testing framework.

Examples in this page will use PHPUnit, but the concepts are applicable at any testing framework.

## Warning

Brain Monkey uses [Patchwork](http://patchwork2.org/) to redefine functions.

Brain Monkey 2.\* requires Patchwork 2 which allows to re-define both userland and core functions, with some [limitations](http://patchwork2.org/limitations/).

The main limitations that affects Brain Monkey are \(from Patchwork website\):

* _Patchwork will fail on every attempt to redefine an internal function that is missing from the redefinable-internals array of your `patchwork.json`._
* _Make sure that Patchwork is imported as early as possible, since any files imported earlier, including the one from which the importing takes place, will be missed by Patchwork's code preprocessor._

## Setup tests

After Brain Monkey is part of the project \(see _Getting Started / Installation_\), to be able to use its features two simple steps are needed before being able to use Brain Monkey in tests:

1. be sure to require Composer autoload file _before_ running tests \(e.g. PHPUnit users will probably require it in their bootstrap file\).
2. call the function `Brain\Monkey\tearDown()` after any test

### PHPUnit example

Let's take PHPUnit as example, the average test case class that uses Brain Monkey would be something like:

```php
use PHPUnit_Framework_TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

class MyTestCase extends PHPUnit_Framework_TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    protected function tearDown()
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}
```

After that for all test classes can extend this class instead of directly extending `PHPUnit_Framework_TestCase`.

That's all. Again, I used PHPUnit for the example, but any testing framework can be used.

For function mocking and testing there are two entry-point functions:

* **`Functions\when()`** 
* **`Functions\expect()`**

See dedicated documentation pages.

## Namespaced functions

All the code examples in this documentation make use of functions in global namespace.

However, note that namespaced functions are supported as well, just be sure to pass the fully qualified name of the functions:

```php
Functions\expect('a_global_function');

Functions\expect('My\\App\\awesome_function');
```

## Note for WordPressers

Anything said in this page is fine for WordPress functions too, they are PHP functions, after all.

However, Brain Monkey has specific features for WordPress, and there is a way to setup tests for **all** Brain Monkey features \(WordPress-specific and not\).

**If you want to use Brain Monkey to test code wrote for WordPress, it is preferable to use the setup explained in the** _**"WordPress / Setup"**_ **section that** _**includes**_ **the setup needed to use Brain Monkey tools for functions.**


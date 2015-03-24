<!--
currentMenu: "functionsetup"
currentSection: "PHP Functions"
title: "Setup Brain Monkey"
-->
# Testing PHP Functions: Setup Brain Monkey

## Testing framework agnostic

Brain Monkey can be used with any testing framework.
Examples in this page will use PHPUnit, but the concepts are applicable at any testing framework.


## Setup tests

After Brain Monkey is part of the project (see *Getting Started / Installation*), to be able to use its features
you need to **require vendor autoload file** before running tests (e.g. PHPUnit users will probably require it in their bootstrap file).

After that you need to call a method *before* any test, and another *after* any test.

These two methods are:

 - `Brain\Monkey\Functions\setUp()` has to be run before any test
 - `Brain\Monkey\Functions\tearDown()` has to be run after any test

### PHPUnit example

Let's take PHPUnit as example, the average test case class that uses Brain Monkey would be something like:

```php
use PHPUnit_Framework_TestCase;
use Brain\Monkey\Functions;

class MyTestCase extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        Functions::setUp();
    }

    protected function tearDown()
    {
        Functions::tearDown();
        parent::tearDown();
    }
}
```

After that for all test classes can extend this class instead of directly extending `PHPUnit_Framework_TestCase`.

That's all. Again, I used PHPUnit for the example, but any testing framework can be used.

Now you are ready to start testing functions.

For the scope there are two entry-point methods of the `Functions` class: **`when()`** and **`expect()`**.
See dedicated doc pages.


## Namespaced functions

All the code examples in this documentation make use of functions in global namespace.
However, note that namespaced functions are supported as well, just be sure to pass the fully qualified name of the function
to `Functions` methods:

```php
Functions::expect('a_global_function');

Functions::expect('My\\App\\awesome_function()');
```


## Note for WordPressers

Anything said in this page is fine for WordPress functions too, they are PHP functions, after all.

However, Brain Monkey has specific features for WordPress, and there is a way to setup tests for **all** Brain Monkey features (WordPress-specific and not).

If you want to use Brain Monkey to test code wrote for WordPress, it is preferable to use the setup explained in the *"WordPress / Setup"* section
that *includes* the setup needed to use Brain Monkey tools for functions.

<!--
currentMenu: "wpsetup"
currentSection: "WordPress"
title: "Setup Brain Monkey for WordPress Tests"
-->

# Setup Brain Monkey for WordPress Tests

## Testing framework agnostic

Brain Monkey can be used with any testing framework.
Examples in this page will use PHPUnit, but the concepts are applicable to any testing framework.

## Warning

The procedure below **includes** the setup needed for testing PHP functions, so there is **no** need to
apply what said here and additionally what said in the section *PHP Functions / Setup*: steps below are enough to use
all Brain Monkey features, including functions utilities.

## Setup tests

After Brain Monkey is part of the project (see *Getting Started / Installation*), to be able to use its features
you need to **require vendor autoload file** before running tests (e.g. PHPUnit users will probably require it in their bootstrap file).

After that you need to call a method *before* any test, and another *after* any test.

These two methods are:

 - `Brain\Monkey\setUpWP()` has to be run before any test
 - `Brain\Monkey\tearDownWP()` has to be run after any test

PHPUnit users will probably want to add these methods to a custom test case class:

```php
use PHPUnit_Framework_TestCase;
use Brain\Monkey;

class MyTestCase extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        Monkey::setUpWP();
    }

    protected function tearDown()
    {
        Monkey::tearDownWP();
        parent::tearDown();
    }
}
```

and then extend various test classes from it instead of directly extend `PHPUnit_Framework_TestCase`.

That's all. You are ready to use all Brain Monkey features.

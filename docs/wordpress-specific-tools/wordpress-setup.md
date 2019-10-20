# Setup for WordPress testing

## Testing framework agnostic

Brain Monkey can be used with any testing framework. Examples in this page will use PHPUnit, but the concepts are applicable to any testing framework.

## Warning

The procedure below **includes** the setup needed for testing PHP functions, so there is **no** need to apply what said here and _additionally_ what said in the section _PHP Functions / Setup_: steps below are enough to use all Brain Monkey features, including functions utilities.

## Setup tests

After Brain Monkey is part of the project \(see _Getting Started / Installation_\), to be able to use its features you need to **require vendor autoload file** before running tests \(e.g. PHPUnit users will probably require it in their bootstrap file\).

After that, you need to call a function _before_ any test, and another _after_ any test.

These two functions are:

* `Brain\Monkey\setUp()` has to be run before any test
* `Brain\Monkey\tearDown()` has to be run after any test

PHPUnit users will probably want to add these methods to a custom test case class:

```php
use PHPUnit_Framework_TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

class MyTestCase extends PHPUnit_Framework_TestCase {

    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    protected function setUp() {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown() {
        Monkey\tearDown();
        parent::tearDown();
    }
}
```

and then extend various test classes from it instead of directly extend `PHPUnit_Framework_TestCase`.

That's all. You are ready to use all Brain Monkey features.


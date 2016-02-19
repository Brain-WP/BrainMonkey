<!--
currentMenu: "functionswhen"
currentSection: "PHP Functions"
title: "Patching functions with when()"
-->
# Patching functions with when()

The first method Brain Monkey offers to monkey patch a function is `when()`.
This method have to be used to **set a behavior** for functions.

`when()` and 5 related methods are used to define functions (if not defined yet) and:

 - make them return a specific value
 - make them return one of the received arguments
 - make them echo a specific value
 - make them echo one of the received arguments
 - make them behave just like another callback


For the sake of readability, in all the code samples below I'll assume that an `use` statement is in place:

```php
use Brain\Monkey\Functions;
```

Don't forget to add it in your code as well, or use the fully qualified class name.
Also be sure to read the *PHP Functions / Setup* section that explain how setup Brain Monkey for usage in tests.


## `justReturn()`

By using `when()` in combination with `justReturn()` you can make a (maybe) undefined function *just return* a given value:


```php
Functions::when('a_undefined_function')->justReturn('Cool!');

echo a_undefined_function(); // echoes "Cool!"
```

Without passing a value to `justReturn()` the target function will return nothing (`null`).


## `returnArg()`

This other when-related method is used to make the target function return one of the received arguments, by default the first.

```php
Functions::when('give_me_the_first')->returnArg(); // is the same of ->returnArg(1)
Functions::when('i_want_the_second')->returnArg(2);
Functions::when('and_the_third_for_me')->returnArg(3);

echo give_me_the_first('A', 'B', 'C'); // echoes "A"
echo i_want_the_second('A', 'B', 'C'); // echoes "B"
echo and_the_third_for_me('A', 'B', 'C'); // echoes "C"
```

Note that if the target function does not receive the desired argument, `returnArg()` throws an exception:

```php
Functions::when('needs_the_third')->returnArg(3);

echo needs_the_third('A', 'B'); // throws an exception because required 3rd argument, but received 2
```

## `justEcho()`

Similar to `justReturn()`, it makes the mocked function echo some value instead of returning it.

```php
Functions::when('a_undefined_function')->justEcho('Cool!');

a_undefined_function(); // echoes "Cool!"
```

## `echoArg()`

Similar to `returnArg()`, it makes the mocked function echo some received argument instead of returning it.

```php
Functions::when('echo_the_first')->echoArg(); // is the same of ->echoArg(1)
Functions::when('echo_the_second')->echoArg(2);

echo_the_first('A', 'B', 'C'); // echoes "A"
echo_the_second('A', 'B', 'C'); // echoes "B"
```

## `alias()`

The last of the when-related methods allows to make a function behave just like another callback.
The replacing function can be anything that can be run: a core function or a custom one, a class method, a closure...

```php
Functions::when('duplicate')->alias(function($value) {
    "Was ".$value.", now is ".($value * 2);
});
Functions::when('bigger')->alias('strtoupper');

echo duplicate(1); // echoes "Was 1, now is 2"
echo bigger('was lower'); // echoes "WAS LOWER"
```

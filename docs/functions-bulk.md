<!--
currentMenu: "functionsbulk"
currentSection: "PHP Functions"
title: "Bulk functions patching"
-->
# Bulk functions patching

`when()` and its related methods are quite simple and straightforward.

However, it can be quite verbose when multiple functions needed to be patched.

When one uses `when()` they is not interested in adding expectations, but more often than not is
interested in just assuring the target function is defined (and maybe) on its return value.

For these reason version 2.1 introduced two new API functions to define multiple functions in bulk:

- `bulkDefine()`
- `bulkDefinePassTrough()`

## `bulkDefine()`

`Functions\bulkDefine()` accepts an array of functions to be defined where keys are function names 
and values are wanted return values:

```php
Functions\bulkDefine([
    'is_user_logged_in' => true,
    'current_user_can'  => true,
]);
```

An interesting feature is that when value is `callable` the given function is aliased to that
callable:

```php
Functions\bulkDefine([
    'is_user_logged_in' => true,
    'current_user_can'  => true,
    'wp_get_current_user' => function() {
        return \Mockery::mock('\WP_User');
    }
]);
```

This means that using `bulkDefine()` is not actually possible to define a function that returns a
callable, in that case `when()` will do just fine.

It is also possible to pass function names as array item _values_ and without keys (implicit 
numeric keys), in that case the return value for the functions will be anything is passed as
second argument to `bulkDefine()`, or `null` if no second argument is used.

Example:

```php
Functions\bulkDefine(['is_user_logged_in','current_user_can'], true);
```

## `bulkDefinePassTrough()`

Many core functions in WP return the first argument passed to them maybe modified in some way.
For testing purposes is often desired that the first argument is returned unchanged, this is
what `when( $function_name )->justReturnArg()` does for _a_ function.

`Functions\bulkDefinePassTrough()` allows to do same thing for _multiple_ functions:

```php
Functions\bulkDefinePassTrough([
    'esc_attr',
    'esc_html',
    'esc_textarea',
    '__',
    '_x',
    'esc_html__',
    'esc_html_x',
    'esc_attr_x',
    'esc_attr_x',
]);
```


<!--
currentMenu: "functionsstubs"
currentSection: "PHP Functions"
title: "Bulk patching with stubs()"
-->
# Bulk patching with stubs()

`when()` and its related methods are quite simple and straightforward.

However, it can be quite verbose when multiple functions needed to be patched.

When one uses `when()` they are not interested in adding expectations but usually are
interested in ensuring the target function is defined, and maybe its return value.

For this reason, version 2.1 introduced a new API function to define multiple functions in bulk: `stubs()`

## `stubs()`

`Functions\stubs()` accepts an array of functions to be defined.

The function names can be passed as array item _keys_ or as array item _values_ (with no key).

When the function name is the item key, the item value can be:

- `a callable`, in which case the function will be aliased to it
- anything else, in which case a stub returning given value will be created for the function

Example:

```php
Functions\stubs([
    'is_user_logged_in'   => true,
    'current_user_can'    => true,
    'wp_get_current_user' => function() {
        return \Mockery::mock('\WP_User');
    }
]);
```

When the function name is the array item value, and no item key is used, the behavior will change 
based on the second param passed to `stubs()`:

- when 2nd param is `null` (default), the created stub will return the 1st param it will receive
- when 2nd param is anything else, the created stub will return just it


Example:

```php
// given functions will return `true`
Functions\stubs(
    [
        'is_user_logged_in',
        'current_user_can'
    ],
    true
);

// given functions will return the first argument they will receive
// just like `when( $function_name )->justReturnArg()` was used
Functions\stubs(
   'esc_attr',
   'esc_html',
   'esc_textarea',
   '__',
   '_x',
   'esc_html__',
   'esc_html_x',
   'esc_attr_x',
);
```

## Gotcha

When passing to `stubs()` a function name as array item key and a `callable` as value, the function
will be aliased to that callable. It means that using `stubs()` is not possible to create a stub
for a function that returns a callback.

Moreover, using a function name as array item value (no key), and passing `null` as second param to
`stubs()`, it will create a stub that returns the first argument received.
It means that using `stubs()` is not possible to create a stub for a function that returns `null`. 

In both cases `when()` will do just fine.

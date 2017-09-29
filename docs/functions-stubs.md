<!--
currentMenu: "functionsstubs"
currentSection: "PHP Functions"
title: "Bulk patching with stubs()"
-->
# Bulk patching with stubs()

`when()` and its related functions are quite simple and straightforward.

However, it can be quite verbose when multiple functions needed to be patched.

When one uses `when()` they are not interested in adding expectations but usually are
interested in ensuring the target function is defined, and maybe its return value.

For this reason, version 2.1 introduced a new API function to define multiple functions in bulk: `stubs()`

## `stubs()`

`Functions\stubs()` accepts an array of functions to be defined.

The function names can be passed as array item _keys_ or as array item _values_ and no key.

When the function name is the item key, the item value can be either:

- a `callable`, in which case the function will be aliased to it
- anything else, in which case a stub returning a given value will be created for the function

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
based on the second argument passed to `stubs()`:

- when the second argument is `null` (default), the created stub will return the first parameter it would receive
- when the second argument is anything else, the created stub will use it as its return value


Example:

```php
// Given functions will return `true`
Functions\stubs(
    [
        'is_user_logged_in',
        'current_user_can',
    ],
    true
);

// Given functions will return the first argument they would receive,
// just like `when( $function_name )->justReturnArg()` was used for all of them.
Functions\stubs(
   [
        'esc_attr',
        'esc_html',
        'esc_textarea',
        '__',
        '_x',
        'esc_html__',
        'esc_html_x',
        'esc_attr_x',
   ]
);
```

### Gotcha

When passing a function name as an array item key and a `callable` as the value, the function
will be aliased to that callable. That means it is **not** possible to create a stub
for a function that returns a callback, by doing something like:

```php
Functions\stubs(
   [ 
        'function_that_returns_a_callback' => 'the_expected_returned_callback'
   ]
);
```

But this will work:

```php
Functions\stubs(
   [
        'function_that_returns_a_callback' => function() { 
            return 'the_expected_returned_callback';
        }
   ]
);
```

Moreover, when doing something like this:

```php
Functions\stubs(
   [ 'function_that_returns_null' => null ]
);
```

or like this:

```php
Functions\stubs(
   [ 'function_that_returns_null' ],
   null
);
```


the return value of the stub will **not** be `null`, because when return value is set to `null` 
Brain Monkey will make the function stub return the first received value.

The only way to use `stubs()` for creating a stub that returns `null` is:

```php
Functions\stubs(
   [ 'function_that_returns_null' => function() { return null; } ]
);
```

or the equivalent but more concise:

```php
// __return_null is defined by Brain Monkey since version 2
Functions\stubs( [ 'function_that_returns_null' => '__return_null' ] );
```

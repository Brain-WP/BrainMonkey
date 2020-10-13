# Bulk patching with `stubs()`

`when()` and its related functions are quite simple and straightforward.

However, it can be quite verbose when multiple functions needs to be patched.

For this reason, version 2.1 introduced a new API function to define multiple functions in bulk: `stubs()`

### `stubs()`

`Functions\stubs()` accepts an array of functions to be defined.

The first way to use it is to pass function names as array item _keys_ and the wanted return values as array _values_:

```php
Functions\stubs(
    [
        'is_user_logged_in' => true,
        'current_user_can'  => false,
    ]
);
```

There are two special cases:

* when the array item value is a `callable`, the function given as array item key is _aliased_ to the given callback instead of returning the callback itself;
* when the array item value is `null`, the function given as array item key will return the first argument received, just like `when( $function_name )->justReturnArg()` was used for it

```php
Functions\stubs(
    [
        'is_user_logged_in'    => true,             // will return `true` as provided
        'wp_get_current_user' => function () {      // will return the WP_User mock
            return \Mockery::mock(\WP_User::class);
        },
        '__' => null,                               // will return the 1st argument received
    ]
);
```

Another way to use `stubs`, useful to stub many function with same return value, is to pass to a non-associative array of function names as first argument, and the wanted return value for all of them as second argument.

For example, the snippet below will create a stub that returns `true` for all the given functions:

```php
Functions\stubs(
    [
        'is_user_logged_in',
        'current_user_can',
        'is_multisite',
        'is_admin',
    ],
    true
);
```

Please note that the default value for the second argument, being it optional, is `null`, and because using `null` as value means _"return first received argument"_ it is possible to stub many functions that have to return first received argument, by passing their names as first argument to `stubs()` \(and no second argument\), like this:

```php
Functions\stubs(
    [
        'esc_attr',
        'esc_html',
        '__',
        '_x',
        'esc_attr__',
        'esc_html__',
    ]
);
```

\(Even if there's a simpler way to stub escaping and translation WP functions, more on this below\).

It worth noting that the two ways of using `stubs()` can be mixed together, for example like this:

```php
Functions\stubs(
    [
        // will both return 1st argument received, because `stubs` 2nd param defaults to `null`
        'esc_attr',
        'esc_html',

        // will all return what is given as array item value
        'is_user_logged_in'   => true,
        'current_user_can'    => false,
        'get_current_user_id' => 1,
    ]
);
```

### Pre-defined stubs for escaping functions

To stub WordPress escaping functions is a very common usage for `Functions\stubs`.

This is why, since version 2.3, Brain Monkey introduced a new API function:

* **`Functions\stubEscapeFunctions()`**

When called, it will create a stub for each of the following functions:

* `esc_js()`
* `esc_sql()`
* `esc_attr()`
* `esc_html()`
* `esc_textarea()`
* `esc_url()`
* `esc_url_raw()`
* `esc_xml()` \(since 2.6\)

By calling `Functions\stubEscapeFunctions()`, for _all_ of the functions listed above a stub will be created that will do some very basic escaping on the received first argument before returning it.

It will _not_ be the exact same escape mechanism that WordPress would apply, but "similar enough" for unit tests purpose and could still be helpful to discover some bugs.

### Pre-defined stubs for translation functions

Another common usage for `Functions\stubs`, since its introduction, has been to stub translation functions.

Since version 2.3, this has became much easier thanks to the introduction of a new API function:

* **`Functions\stubTranslationFunctions()`**

When called, it will create a stub for _all_ the following functions:

* `__()`
* `_e()`
* `_ex()`
* `_x()`
* `_n()` \(since 2.6\)
* `_nx()` \(since 2.6\)
* `translate()`
* `esc_html__()`
* `esc_html_x()`
* `esc_attr__()` 
* `esc_attr_x()` 
* `esc_html_e()` 
* `esc_attr_e()` 

The created stub will not attempt any translation, but will return \(or echo\) the first received argument.

Only for functions that both translate and escape \(`esc_html__()`, `esc_html_x()`...\) the same escaping mechanism used by the pre-defined escaping functions stubs \(see above\) is applied before returning first received argument.

Please note how `Functions\stubTranslationFunctions()` creates stubs for functions that _echo_ translated text, something not easily doable with `Functions\stubs()` alone.

### Gotcha for `Functions\stubs`

#### Functions that returns null

When using `stubs()`, passing `null` as the "value" of the function to stub, the return value of the stub will **not** be `null`, but the first received value.

To use `stubs()` to stub functions that return `null` it is possible to do something like this:

```php
Functions\stubs( [ 'function_that_returns_null' => '__return_null' ] );
```

It works because `__return_null` is a WP function that Brain Monkey also defines since version 2.0.

#### Functions that returns callbacks

When using `stubs`, passing a `callable` as the "value" of the function to stub, the created stub will be an _alias_ of the given callable, will **not** return it.

If one want to use `stubs` to stub a function that returns a callable, a way to do it would be something like this:

```php
Functions\stubs(
   [
        'function_that_returns_a_callback' => function() { 
            return 'the_expected_returned_callback';
        }
   ]
);
```

but it is probably simpler to use the "usual" `when` + `justReturn`:

```php
when('function_that_returns_a_callback')->justReturn('the_expected_returned_callback')
```

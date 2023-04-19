# WordPress testing tools

The sole ability to mocking functions is a great help on testing WordPress code.

All WordPress functions can be mocked and tested using the techniques described in the _PHP Functions_ section, they are PHP functions, after all.

However, to test WordPress code in isolation, without a bunch of bootstrap code for every test, a more fine grained control of plugin API functions is required.

This is exactly what Brain Monkey offers.

## Defined functions

Following functions are defined by Brain Monkey when it is loaded for tests:

**Hook-related functions:**

* `add_action()`
* `remove_action()`
* `do_action()`
* `do_action_ref_array()`
* `do_action_deprecated()`  (since 2.4)
* `did_action()`
* `doing_action()`
* `has_action()`
* `add_filter()`
* `remove_filter()`
* `apply_filters()`
* `apply_filters_ref_array()`
* `apply_filters_deprecated()` \(since 2.4\)
* `doing_filter()`
* `has_filter()`
* `current_filter()`

**Generic functions:**

* `__return_true()`
* `__return_false()`
* `__return_null()`
* `__return_zero()`
* `__return_empty_array()`
* `__return_empty_string()`
* `trailingslashit()`
* `untrailingslashit()`
* `user_trailingslashit()` \(since 2.6\)
* `absint()` \(since 2.3\)
* `wp_json_encode()` \(since 2.6\)
* `is_wp_error()` \(since 2.3\)
* `wp_validate_boolean()` \(since 2.7\)
* `wp_slash()` \(since 2.7\)

**Translation function:**

Since Brain Monkey 2.3, stubs for the standard WordPress translations functions are available via `Functions\stubEscapeFunctions()`.
See: [Pre-defined stubs for translation functions](https://giuseppe-mazzapica.gitbook.io/brain-monkey/functions-testing-tools/function-stubs#pre-defined-stubs-for-translation-functions)

**Escaping functions:**
Since Brain Monkey 2.3, stubs for the standard WordPress escaping functions are available via `Functions\stubTranslationFunctions()`.
See: [Pre-defined stubs for escaping functions](https://giuseppe-mazzapica.gitbook.io/brain-monkey/functions-testing-tools/function-stubs#pre-defined-stubs-for-escaping-functions)

If your code uses any of these functions, and very likely it does, you don't need to define \(or mock\) them to avoid fatal errors during tests.

Note that the returning value of those functions \(_most of the times_\) will work out of the box as you might expect.

For example, if your code contains:

```php
do_action('my_custom_action');

// something in the middle
$did = did_action('my_custom_action');
```

the value of `$did` will be correctly `1` \(`did_action()` in WordPress returns the number an action was _done_\).

Or if your code contains:

```php
$post = [ 'post_title' => 'My Title' ];

$title = apply_filters('the_title', $post['post_title']);
```

the value of `$title` will be `'My Title'`, without the need of any intervention.

This works as long as there's no code that actually adds filters to `"the_title"` hook, so we expect that the title stay unchanged. And that's what happen.

If in the code under test there's something that adds filters \(i.e. calls `add_filter`\), the _Brain Monkey version_ of `apply_filters` will still return the value unchanged, but will allow to test that `apply_filters` has been called, how many times, with which callbacks and arguments are used.

More generally, with regards to the WP hook API, Brain Monkey allows to:

* test if an action or a filter has been added, how many times that happen and with which arguments
* test if an action or a filter has been fired, how many times that happen and with which arguments
* perform some callback when an action is fired, being able to access passed arguments
* perform some callback when an filter is applied, being able to access passed arguments and to return specific values

And it does that using its straightforward and human-readable syntax.


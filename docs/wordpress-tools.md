<!--
currentMenu: "wptools"
currentSection: "WordPress"
title: "WordPress Testing Tools"
-->
# WordPress Testing Tools

The sole ability to mocking functions is a great help on testing WordPress code.

All WordPress functions can be mocked and tested using the techniques described in the *PHP Functions* section, they are PHP functions, after all.

However, to test WordPress code in isolation, without a bunch of bootstrap code for every test, a more fine grained control of plugin API functions is required.

This is exactly what Brain Monkey offers.



## Defined functions

Following functions are defined by Brain Monkey when it is loaded for tests:



**Hook-related functions:**

 - `add_action()`
 - `remove_action()`
 - `do_action()`
 - `do_action_ref_array()`
 - `did_action()`
 - `doing_action()`
 - `has_action()`
 - `add_filter()`
 - `remove_filter()`
 - `apply_filters()`
 - `apply_filters_ref_array()`
 - `has_filter()`
 - `current_filter()`

**Generic functions:**

 - `__return_true()`
 - `__return_false()`
 - `__return_null()`
 - `__return_empty_array()`
 - `__return_empty_string()`
 - `trailingslashit()`
 - `untrailingslashit()`



If your code uses any of these functions, and very likely it does, you don't need to define (or mock) them
to avoid fatal errors during tests.

Note that the returning value of those functions (most of the times) will work out of the box as you might expect.

For example, if your code contains:

```php
do_action('my_custom_action');

// something in the middle
$did = did_action('my_custom_action');
```
the value of `$did` will be correctly `1` (`did_action()` in WordPress returns the number an action was *done*).

Or if your code contains:

```php
$post = [ 'post_title' => 'My Title' ];

$title = apply_filters('the_title', $post['post_title']);
```
the value of `$title` will be `'My Title'`, without the need of any intervention.

But, of course, that's not enough. To proper test WordPress code you will probably desire to:

 - test if an action or a filter has been added, how many times that happen and with which arguments
 - test if an action or a filter has been fired, how many times that happen and with which arguments
 - perform some callback when an action is fired, being able to access passed arguments
 - perform some callback when an filter is applied, being able to access passed arguments and to return specific values

Guess what, Brain Monkey allows to do all of this and even more.

And it does that using its straightforward and human readable syntax.
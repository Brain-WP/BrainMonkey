<!--
currentMenu: "wptools"
currentSection: "WordPress"
title: "WordPress Testing Tools"
-->
# WordPress Testing Tools

The sole ability to mocking function is a great help on testing WordPress code.

All WordPress functions can be mocked and tested using the techniques described in the *PHP Functions* section, they are PHP functions, after all.

However, to test WordPress code in isolation, without a bunch of bootstrap code for every test, a more fine grained control of plugin API functions is required.

This is exactly what Brain Monkey offers.

## Defined functions

Following functions are defined by Brain Monkey when it is loaded for tests:

 - `add_action()`
 - `remove_action()`
 - `do_action()`
 - `do_action_ref_array()`
 - `did_action()`
 - `has_action()`
 - `add_filter()`
 - `remove_filter()`
 - `apply_filters()`
 - `apply_filters_ref_array()`
 - `has_filter()`
 - `current_filter()`

If your code uses any of these functions, and very likely it does, you don't need to define (or mock) them
to avoid fatal errors during tests.

Note that the returning value of those function (most of the times) will work out of the box as you might expect.

For example, if your code contains:

```php
do_action('my_custom_action');
// something in the middle
$did = did_action('my_custom_action');
```
the value of `$did` will be correctly `1` (`did_action()` in WordPress returns the number an action was *done*).

Or if your code contains:

```php
$post = array(
  'post_title' => 'My Title',
);
$title = apply_filters('the_title', $post['post_title']);
```
the value of `$title` will be `'My Title'`, without the need of any intervention.

But, of course, that's not enough. To proper test WordPress code you will probably desire to:

 - test if an action or a filter has been added, how many times that happen and with which arguments
 - test if an action or a filter has been fired, how many times that happen and with which arguments
 - perform some callback when an action is fired, being able to access passed arguments
 - perform some callback when an filter is applied, being able to access passed arguments and to return specific values

guess what, Brain Monkey allows to do all of this and even more. And it does that using its straightforward and human readable syntax.

## Note on Brain Monkey classes

After Brain Monkey is properly setup to be used in tests (see *Wordpress / Setup*) it is possible to access to its features in 2 ways:

 - by using the `Brain\Monkey` class that gives access to all the features
 - by using 3 feature-specific classes, they are:
   - `Brain\Monkey\Functions`, used to mock and tests functions. Its usage is documented in the *PHP Functions* docs section
   - `Brain\Monkey\Actions`, used to test action hooks
   - `Brain\Monkey\Filters`, used to test filter hooks

What is possible to do with one method is also possible with the other, it's just a matter of preference.

Using first method, test classes will probably contain just one `use` statement instead of three; using feature-specific classes
needed code is a bit less verbose.

Below there are a few examples of both methods. The code assumes proper `use` statements are in place.

Functions examples:

```php
// method one
Monkey::functions()->expect('register_post_type')->once()->andReturn(true);

// method two
Functions::expect('register_post_type')->once()->andReturn(true);
```

Action and Filters examples:

```php
// actions, method one
Monkey::actions()->expectAdded('init')->once();

// actions, method two
Actions::expectAdded('init')->once();

// filters, method one
Monkey::filters()->expectApplied('the_title')->andReturn('My Post Title');

// filters, method two
Filters::expectApplied('the_title')->andReturn('My Post Title');
```

Again, the two methods are perfectly equivalent.

In the rest of the docs I'll mostly use feature-specific method, but feel free to use the one you prefer.

<!--
currentMenu: "whatwhy"
currentSection: "Getting Started"
title: "What & Why"
-->
# What's Brain Monkey

Brain Monkey is a tests utility for PHP.

It comes with 2 group of features:

 - the first allow **mocking and testing any PHP function**. This part is a general tool and 2 times framework agnostic:
   can be used to test code that uses any frameworks (or no framework) and in combination with any testing framework.
 - the second group of features can be used with any testing framework as well, but is **specific to test WordPress code**.
   Who is interested in the first part can use only it, just like this second group of features does not exists.


# Why Brain Monkey

When unit tests are done in the right way, the SUT (System Under Test) must be tested in **isolation**.
Long story short, it means that any *external* code used in the SUT must be assumed as perfectly working.

This is a key concept in unit tests.

In PHP, to create mock and stub objects for the scope is a pretty easy task, framework like [PHPUnit](https://phpunit.de/manual/current/en/test-doubles.html) or [phpspec](http://www.phpspec.net/en/latest/manual/prophet-objects.html) have embedded features to do that, and libraries like
[Mockery](https://github.com/padraic/mockery) make it even easier.

But when *external* code make use of **functions** things become harder, because PHP testing framework can't mock or monkey patch functions.

This is where Brain Monkey comes into play: its aim is to bring that easiness to function testing.

This involves:

 - define functions if not defined
 - allow to enforce function behavior
 - allow to set expectations on function execution

Moreover, I have to admit that I coded Brain Monkey to test WordPress code (that makes a large use of global functions).

This is the reason why Brain Monkey comes with a set of WordPress-specific tools, but the ability to monkey patch and test functions
is independent from WordPress-specific tools and can be used to test any PHP code.


## Under the hood

All the Brain Monkey code for testing functions is a **single class** with about 100 lines of code.

It gets all the power it brings to you from two great libraries: [**Mockery**](http://docs.mockery.io/) and [**Patchwork**](http://antecedent.github.io/patchwork/).

What actually Brain Monkey does is to connect the *function redefinition* feature of Patchwork with the powerful testing mechanism provided by Mockery,
and thanks to that Brain Monkey has:

 - PHPUnit, PHPSpec or any other testing framework compatibility
 - powerful and succinct API with human readable syntax

All the rest is joy.


## PHP versions compatibility

Currently, Brain Monkey supports PHP from 5.4.x to 5.6.x.

At the moment, HHVM is **not** supported for function testing, but WordPress plugin API test utilities works seamlessly even there.

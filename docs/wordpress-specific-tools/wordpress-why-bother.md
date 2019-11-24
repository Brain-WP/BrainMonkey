# Why bother

Just to be clear, Brain Monkey is useful for testing code wrote _for_ WordPress \(plugin, themes\) not WordPress core.

More specifically, it is useful to run **unit tests**.

Integration tests or end-to-end tests are a thing: you need to be sure that your code works good _with_ WordPress.

But **unit** tests are meant to be run **without loading WordPress environment**.

Every component that is unit tested, should be tested in isolation: when you test a class, you only have to test that specific class, assuming all other code \(e.g. WordPress code\) is working perfectly.

This is not only because doing that tests will run much faster, but also because the key concept in unit testing is that every piece of code should work _per se_, in this way if a test fails there is only one possible culprit.

By assuming all the external code is working perfectly, it is possible to test the behavior of the SUT \(System Under Test\), without any _interference_.

To deepen these concepts, read [this answer](https://wordpress.stackexchange.com/a/164138/35541) I wrote for WordPress Development \(StackExchange\) site, that also contains some tips to write better _testable_ WordPress code.

## If WordPress is not loaded...

WordPress functions are not available, and trying to run tests in that situation, tests fail with fatal errors.

Unless you use Brain Monkey.

It allows to mock WordPress function \(just like any PHP function\), and to check how they are called inside your code.

See the _PHP Function_ documentation section for a deep explanation on how it works.

Moreover, among others, WordPress [Plugin API functions](https://codex.wordpress.org/Plugin_API) are particularly important and a very fine grained control on how they are used in code is pivotal to proper test WordPress extensions.

This is why Brain Monkey comes with a set of features specifically designed for that.


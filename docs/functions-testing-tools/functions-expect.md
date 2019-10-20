# Testing functions with expect\(\)

Often, in tests, what we need is not only to enforce a function returned value \(what `Functions\when()` allows to do\), but to test function behavior based on **expectations**.

Mockery has a very powerful, and human readable Domain Specific Language \(DSL\) that allows to set expectations on how object methods should behave, e.g. validate arguments they should receive, how many times they are called, and so on.

Brain Monkey brings that power to function testing. The entry-point is the `Functions\expect()` function.

It receives a function name and returns a Mockery expectation object with all its power.

Below there are just several examples, for the full story about Mockery expectations see its [documentation](http://docs.mockery.io/en/latest/reference/index.html).

Only note that in functions testing the `shouldReceive` Mockery method makes **no sense**, so don't use it \(an exception will be thrown if you do that\).

## Expectations on times a function is called

```php
Functions\expect('paganini')->once();

Functions\expect('tween')->twice();

Functions\expect('who_knows')->zeroOrMoreTimes();

Functions\expect('i_should_run')->atLeast()->once();

Functions\expect('i_have_a_max')->atMost()->twice();

Functions\expect('poor_me')->never();

Functions\expect('pretty_precise')->times(3);

Functions\expect('i_have_max_and_min')->between(2, 4);
```

There is no need to explain how it works: Mockery DSL reads like plain English.

Of course, expectation on the times a function should run can be combined with arguments expectation.

## Expectations on received arguments

Below a few examples, for the full story see [Mockery docs](http://docs.mockery.io/en/latest/reference/argument_validation.html).

```php
// allow anything
Functions\expect('function_name')
  ->once()
  ->withAnyArgs();

// allow nothing
Functions\expect('function_name')
  ->once()
  ->withNoArgs();

// validate specific arguments
Functions\expect('function_name')
  ->once()
  ->with('arg_1', 'arg2');

// validate specific argument types
Functions\expect('function_name')
  ->times(3)
  ->with(Mockery::type('resource'), Mockery::type('int'));

// validate anything in specific places
Functions\expect('function_name')
    ->zeroOrMoreTimes()
    ->with(Mockery::any());

// validate a set of given arguments
Functions::expect('function_name')
    ->once()
    ->with(Mockery::anyOf('a', 'b', 'c'));

// regex validation
Functions\expect('function_name')
    ->once()
    ->with('/^foo/');

// excluding specific values
Functions\expect('function_name')
    ->once()
    ->with(Mockery::not(2, 3));

// dealing with array arguments
Functions\expect('function_name')
    ->once()
    ->with(Mockery::hasKey('foo'), Mockery::contains('bar', 'baz'));
```

## Forcing behavior

Excluding `shouldReceive`, all the Mockery expectation methods can be used with Brain Monkey, including `andReturn` or `andReturnUsing` used to enforce a function to return specific values during tests.

In fact, `Functions\when()` do same thing for simple cases when no expectations are required.

Again, just a few examples:

```php
// return a specific value
Functions\expect('function_name')
  ->once()
  ->with('foo', 'bar')
  ->andReturn('Baz!');

// return values in order
Functions\expect('function_name')
  ->twice()
  ->andReturn('First time I run', 'Second time I run');

// return values in order, alternative
Functions\expect('function_name')
  ->twice()
  ->andReturnValues(['First time I run', 'Second time I run']);

// return noting
Functions::expect('function_name')
  ->twice()
  ->andReturnNull();

// use a callback for returning a value
Functions\expect('function_name')
  ->atLeast()
  ->once()
  ->andReturnUsing(function() {
      return 'I am an alias!';
  });

// makes function throws an Exception (e.g. to test try statements)
Functions\expect('function_name')
  ->once()
  ->andThrow('RuntimeException'); // Both exception names and object are supported
```


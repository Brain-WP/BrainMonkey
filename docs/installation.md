# Installation

To install Brain Monkey you need:

* PHP 5.6+
* [Composer](https://getcomposer.org)

Brain Monkey is available on Packagist, so the only thing you need to do is to add it as a dependency for your project.

That can be done by running following command in your project folder:

```text
composer require brain/monkey:2.* --dev
```

As alternative you can directly edit your `composer.json` by adding:

```javascript
{
  "require-dev": {
    "brain/monkey": "~2.0.0"
  }
}
```

I've used `require-dev` because, being a testing tool, Brain Monkey should **not** be included in production.

Brain Monkey can work with any testing framework, so it doesn't require any of them.

To run your tests you'll probably need to require a testing framework too, e.g. [PHPUnit](https://phpunit.de/) or [phpspec](https://www.phpspec.net/en/latest/).

## Dependencies

Brain Monkey needs 2 libraries to work:

* [Mockery](http://docs.mockery.io/en/latest/) \(BSD-3-Clause\)
* [Patchwork](http://patchwork2.org/) \(MIT\)

They will be installed for you by Composer.

When installed in development mode \(to test itself\), Brain Monkey also requires:

* [PHPUnit](https://phpunit.de/) \(MIT\)


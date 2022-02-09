<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Name;

/**
 * Provides a string representation for a callback.
 *
 * Callbacks are not checked for real callable capability, but only for syntax.
 * E.g. something like `new CallbackStringForm(['FooClass', 'foo_method'])` would not raise any
 * error even if the class is not available.
 * However, `new CallbackStringForm(['FooClass', 'foo-method'])` would raise an error for invalid
 * method name.
 *
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class CallbackStringForm
{
    /**
     * @var string
     */
    private $parsed;

    /**
     * @param callable $callback
     */
    public function __construct($callback)
    {
        $this->parsed = $this->parseCallback($callback);
    }

    /**
     * @param \Brain\Monkey\Name\CallbackStringForm $callback
     * @return bool
     */
    public function equals(CallbackStringForm $callback)
    {
        return (string)$this === (string)$callback;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->parsed;
    }

    /**
     * @param mixed $callback
     * @return string
     */
    private function parseCallback($callback)
    {
        if (!is_callable($callback, true)) {
            throw Exception\InvalidCallable::forCallable($callback);
        }

        if (is_string($callback)) {
            return $this->parseString($callback);
        }

        $isObject = is_object($callback);

        if ($isObject && !is_callable($callback)) {
            throw new Exception\NotInvokableObjectAsCallback();
        }

        if ($isObject) {
            return $callback instanceof \Closure
                ? (string)new ClosureStringForm($callback)
                : get_class($callback) . '()';
        }

        list($object, $method) = $callback;

        $methodName = (new MethodName($method))->name();

        if (is_string($object)) {
            $className = (new ClassName($object))->fullyQualifiedName();

            $this->assertMethodCallable($className, $methodName, $callback);

            return "{$className}::{$methodName}()";
        }

        if (!is_callable([$object, $methodName])) {
            throw new Exception\NotInvokableObjectAsCallback();
        }

        $className = (new ClassName(get_class($object)))->fullyQualifiedName();

        return ltrim("{$className}->{$methodName}()", '\\');
    }

    /**
     * @param string $callback
     * @return string
     */
    private function parseString($callback)
    {
        $callback = trim($callback);

        if (
            (strpos($callback, 'function') === 0 || strpos($callback, 'static') === 0)
            && substr($callback, -1) === ')'
        ) {
            try {
                return ClosureStringForm::normalizeString($callback);
            } catch (Exception\Exception $exception) {
                throw Exception\InvalidCallable::forCallable($callback);
            }
        }

        $isStaticMethod = substr_count($callback, '::') === 1;
        $isNormalizedForm = substr($callback, -2) === '()';

        // Callback is a static method passed as string, like "Foo\Bar::some_method"
        if ($isStaticMethod && !$isNormalizedForm) {
            return $this->parseCallback(explode('::', $callback));
        }

        // If this is not a string in normalized form, we just check is a valid function name
        if (!$isNormalizedForm) {
            return (new FunctionName($callback))->fullyQualifiedName();
        }

        // remove parenthesis
        $callback = preg_replace('~\(\)$~', '', $callback);

        $isDynamicMethod = substr_count($callback, '->') === 1;

        // If this is a normalized form of a static or dynamic method let's check that both class
        // and method names are fine
        if ($isDynamicMethod || $isStaticMethod) {
            $separator = $isDynamicMethod ? '->' : '::';
            list($class, $method) = explode($separator, $callback);
            $className = (new ClassName($class))->fullyQualifiedName();
            $methodName = (new MethodName($method))->name();
            $this->assertMethodCallable($className, $method, "{$callback}()");

            return ltrim("{$className}{$separator}{$methodName}()", '\\');
        }

        // Last chance is that the string is fully qualified name of an invokable object.
        $className = (new ClassName($callback))->fullyQualifiedName();
        // Check `__invoke` method existence only if class is available
        if (class_exists($className) && !method_exists($className, '__invoke')) {
            throw new Exception\NotInvokableObjectAsCallback();
        }

        return ltrim("{$className}()", '\\');
    }

    /**
     * Ensure method existence only if class is available.
     *
     * @param string $className
     * @param string $method
     * @param string|array $callable
     */
    private function assertMethodCallable($className, $method, $callable)
    {
        if (
            class_exists($className)
            && !(method_exists($className, $method) || is_callable([$className, $method]))
        ) {
            throw Exception\InvalidCallable::forCallable($callable);
        }
    }
}

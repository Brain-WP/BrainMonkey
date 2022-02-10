<?php

/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\Expectation;

use Brain\Monkey\Name\FunctionName;

/**
 * Value object for Brain Monkey expectations targets.
 *
 * Holds the name (either function name or hook name) and the type of expectations.
 * Supported types are hold in class constants.
 *
 * Name of functions and hooks are "normalized" to be used as method names (for mock class).
 *
 * @package Brain\Monkey
 * @license http://opensource.org/licenses/MIT MIT
 */
final class ExpectationTarget
{
    const TYPE_ACTION_ADDED = 'add_action';
    const TYPE_ACTION_DONE = 'do_action';
    const TYPE_ACTION_REMOVED = 'remove_action';
    const TYPE_FILTER_ADDED = 'add_filter';
    const TYPE_FILTER_APPLIED = 'apply_filters';
    const TYPE_FILTER_REMOVED = 'remove_filter';
    const TYPE_FUNCTION = 'function';
    const TYPE_NULL = '';

    const TYPES = [
        self::TYPE_FUNCTION,
        self::TYPE_ACTION_ADDED,
        self::TYPE_ACTION_DONE,
        self::TYPE_ACTION_REMOVED,
        self::TYPE_FILTER_ADDED,
        self::TYPE_FILTER_APPLIED,
        self::TYPE_FILTER_REMOVED,
    ];

    const HOOK_SANITIZE_MAP = [
        '-' => '_hyphen_',
        ' ' => '_space_',
        '/' => '_slash_',
        '\\' => '_backslash_',
        '.' => '_dot_',
        '!' => '_exclamation_',
        '"' => '_double_quote_',
        '\'' => '_quote_',
        '£' => '_pound_',
        '$' => '_dollar_',
        '%' => '_percent_',
        '=' => '_equal_',
        '?' => '_question_',
        '*' => '_asterisk_',
        '@' => '_slug_',
        '#' => '_sharp_',
        '+' => '_plus_',
        '|' => '_pipe_',
        '<' => '_lt_',
        '>' => '_gt_',
        ',' => '_comma_',
        ';' => '_semicolon_',
        ':' => '_colon_',
        '~' => '_tilde_',
        '(' => '_bracket_open_',
        ')' => '_bracket_close_',
        '[' => '_square_bracket_open_',
        ']' => '_square_bracket_close_',
        '{' => '_curly_bracket_open_',
        '}' => '_curly_bracket_close_',
    ];

    const PREFIX_MAP = [
        ExpectationTarget::TYPE_FUNCTION => '',
        ExpectationTarget::TYPE_ACTION_ADDED => 'add_action_',
        ExpectationTarget::TYPE_ACTION_DONE => 'do_action_',
        ExpectationTarget::TYPE_ACTION_REMOVED => 'remove_action_',
        ExpectationTarget::TYPE_FILTER_ADDED => 'add_filter_',
        ExpectationTarget::TYPE_FILTER_APPLIED => 'apply_filters_',
        ExpectationTarget::TYPE_FILTER_REMOVED => 'remove_filter_',
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $originalName;

    /**
     * @param string $type
     * @param string $name
     */
    public function __construct($type, $name)
    {
        if (!in_array($type, self::TYPES, true)) {
            throw Exception\InvalidExpectationType::forType($name);
        }

        if (!is_string($name)) {
            throw Exception\InvalidExpectationName::forNameAndType($name, $type);
        }

        $this->type = $type;

        if ($type === self::TYPE_FUNCTION) {
            $nameObject = new FunctionName($name);
            $namespace = str_replace('\\', '_', ltrim($nameObject->getNamespace(), '\\'));
            $this->originalName = $nameObject->fullyQualifiedName();
            $this->name = $namespace
                ? "{$namespace}_" . $nameObject->shortName()
                : $nameObject->shortName();

            return;
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '__', strtr($name, self::HOOK_SANITIZE_MAP));
        if (!is_string($safeName)) {
            throw Exception\InvalidExpectationName::forNameAndType($name, $type);
        }
        $this->originalName = $name;
        $this->name = $safeName;
    }

    /**
     * @return string
     */
    public function identifier()
    {
        return md5($this->originalName . $this->type);
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function mockMethodName()
    {
        $name = $this->name();
        $type = $this->type();

        if (!array_key_exists($type, self::PREFIX_MAP)) {
            throw new \UnexpectedValueException(sprintf('Unexpected %s type.', __CLASS__));
        }

        return self::PREFIX_MAP[$type] . $name;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @param ExpectationTarget $target
     * @return bool
     */
    public function equals(ExpectationTarget $target)
    {
        return
            ($this->originalName === $target->originalName)
            && ($this->type === $target->type);
    }
}

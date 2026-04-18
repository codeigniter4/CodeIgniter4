<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\CLI\Input;

use CodeIgniter\CLI\Exceptions\InvalidOptionDefinitionException;

/**
 * Value object describing a single option declared by a command.
 */
final readonly class Option
{
    /**
     * @var non-empty-string
     */
    public string $name;

    /**
     * @var non-empty-string|null
     */
    public ?string $shortcut;

    public bool $acceptsValue;

    /**
     * @var non-empty-string|null
     */
    public ?string $valueLabel;

    /**
     * @var non-empty-string|null
     */
    public ?string $negation;

    /**
     * @var bool|list<string>|string|null
     */
    public array|bool|string|null $default;

    /**
     * @param bool|list<string>|string|null $default
     *
     * @throws InvalidOptionDefinitionException
     */
    public function __construct(
        string $name,
        ?string $shortcut = null,
        public string $description = '',
        bool $acceptsValue = false,
        public bool $requiresValue = false,
        ?string $valueLabel = null,
        public bool $isArray = false,
        public bool $negatable = false,
        array|bool|string|null $default = null,
    ) {
        if (str_starts_with($name, '--')) {
            $name = substr($name, 2);
        }

        if ($name === '') {
            throw new InvalidOptionDefinitionException(lang('Commands.emptyOptionName'));
        }

        if (preg_match('/^-|[^a-zA-Z0-9_-]/', $name) !== 0) {
            throw new InvalidOptionDefinitionException(lang('Commands.invalidOptionName', [$name]));
        }

        if ($name === 'extra_options') {
            throw new InvalidOptionDefinitionException(lang('Commands.reservedOptionName'));
        }

        $this->name = $name;

        if ($shortcut !== null) {
            if (str_starts_with($shortcut, '-')) {
                $shortcut = substr($shortcut, 1);
            }

            if ($shortcut === '') {
                throw new InvalidOptionDefinitionException(lang('Commands.emptyShortcutName'));
            }

            if (preg_match('/[^a-zA-Z0-9]/', $shortcut) !== 0) {
                throw new InvalidOptionDefinitionException(lang('Commands.invalidShortcutName', [$shortcut]));
            }

            if (strlen($shortcut) > 1) {
                throw new InvalidOptionDefinitionException(lang('Commands.invalidShortcutNameLength', [$shortcut]));
            }
        }

        $this->shortcut = $shortcut;

        // A "requires value" or "is array" option implicitly accepts a value.
        $acceptsValue = $acceptsValue || $requiresValue || $isArray;

        $this->acceptsValue = $acceptsValue;

        if ($isArray && $negatable) {
            throw new InvalidOptionDefinitionException(lang('Commands.negatableOptionCannotBeArray', [$name]));
        }

        if ($acceptsValue && $negatable) {
            throw new InvalidOptionDefinitionException(lang('Commands.negatableOptionMustNotAcceptValue', [$name]));
        }

        if ($isArray && ! $requiresValue) {
            throw new InvalidOptionDefinitionException(lang('Commands.arrayOptionMustRequireValue', [$name]));
        }

        if (! $acceptsValue && ! $negatable && $default !== null) {
            throw new InvalidOptionDefinitionException(lang('Commands.optionNoValueAndNoDefault', [$name]));
        }

        if ($requiresValue && ! $isArray && ! is_string($default)) {
            throw new InvalidOptionDefinitionException(lang('Commands.optionRequiresStringDefaultValue', [$name]));
        }

        if ($negatable && ! is_bool($default)) {
            throw new InvalidOptionDefinitionException(lang('Commands.negatableOptionInvalidDefault', [$name]));
        }

        if ($isArray) {
            if ($default !== null && ! is_array($default)) {
                throw new InvalidOptionDefinitionException(lang('Commands.arrayOptionInvalidDefault', [$name]));
            }

            if ($default === []) {
                throw new InvalidOptionDefinitionException(lang('Commands.arrayOptionEmptyArrayDefault', [$name]));
            }

            $default ??= [];
        }

        $this->valueLabel = $acceptsValue ? ($valueLabel ?? $name) : null;
        $this->negation   = $negatable ? sprintf('no-%s', $name) : null;
        $this->default    = $acceptsValue || $negatable ? $default : false;
    }
}

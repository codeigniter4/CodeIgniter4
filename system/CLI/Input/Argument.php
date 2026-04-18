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

use CodeIgniter\CLI\Exceptions\InvalidArgumentDefinitionException;

/**
 * Value object describing a single positional argument declared by a spark command.
 */
final readonly class Argument
{
    /**
     * @var non-empty-string
     */
    public string $name;

    /**
     * @var list<string>|string|null
     */
    public array|string|null $default;

    /**
     * @param list<string>|string|null $default
     *
     * @throws InvalidArgumentDefinitionException
     */
    public function __construct(
        string $name,
        public string $description = '',
        public bool $required = false,
        public bool $isArray = false,
        array|string|null $default = null,
    ) {
        if ($name === '') {
            throw new InvalidArgumentDefinitionException(lang('Commands.emptyArgumentName'));
        }

        if (preg_match('/[^a-zA-Z0-9_-]/', $name) !== 0) {
            throw new InvalidArgumentDefinitionException(lang('Commands.invalidArgumentName', [$name]));
        }

        if ($name === 'extra_arguments') {
            throw new InvalidArgumentDefinitionException(lang('Commands.reservedArgumentName'));
        }

        $this->name = $name;

        if ($this->isArray && $this->required) {
            throw new InvalidArgumentDefinitionException(lang('Commands.arrayArgumentCannotBeRequired', [$this->name]));
        }

        if ($this->required && $default !== null) {
            throw new InvalidArgumentDefinitionException(lang('Commands.requiredArgumentNoDefault', [$this->name]));
        }

        if ($this->isArray) {
            if ($default !== null && ! is_array($default)) {
                throw new InvalidArgumentDefinitionException(lang('Commands.arrayArgumentInvalidDefault', [$this->name]));
            }

            $default ??= [];
        } elseif (! $this->required) {
            if ($default === null) {
                throw new InvalidArgumentDefinitionException(lang('Commands.optionalArgumentNoDefault', [$this->name]));
            }

            if (is_array($default)) {
                throw new InvalidArgumentDefinitionException(lang('Commands.nonArrayArgumentWithArrayDefault', [$this->name]));
            }
        }

        $this->default = $default;
    }
}

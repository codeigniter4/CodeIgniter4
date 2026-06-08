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

namespace CodeIgniter\CLI\Attributes;

use Attribute;
use CodeIgniter\Exceptions\LogicException;

/**
 * Attribute to mark a class as a CLI command.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Command
{
    private const NAME_PATTERN = '/^[^\s\:]++(\:[^\s\:]++)*$/';

    /**
     * @var non-empty-string
     */
    public string $name;

    /**
     * @var list<non-empty-string>
     */
    public array $aliases;

    /**
     * @param list<string> $aliases
     *
     * @throws LogicException
     */
    public function __construct(
        string $name,
        public string $description = '',
        public string $group = '',
        array $aliases = [],
    ) {
        if ($name === '') {
            throw new LogicException(lang('Commands.emptyCommandName'));
        }

        if (preg_match(self::NAME_PATTERN, $name) !== 1) {
            throw new LogicException(lang('Commands.invalidCommandName', [$name]));
        }

        $this->name = $name;

        $seen = [];

        foreach ($aliases as $alias) {
            if ($alias === '' || preg_match(self::NAME_PATTERN, $alias) !== 1) {
                throw new LogicException(lang('Commands.invalidCommandAlias', [$alias]));
            }

            if ($alias === $name) {
                throw new LogicException(lang('Commands.commandAliasSameAsName', [$alias]));
            }

            if (isset($seen[$alias])) {
                throw new LogicException(lang('Commands.duplicateCommandAlias', [$alias]));
            }

            $seen[$alias] = true;
        }

        $this->aliases = array_values($aliases);
    }
}

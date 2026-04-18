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
    /**
     * @var non-empty-string
     */
    public string $name;

    /**
     * @throws LogicException
     */
    public function __construct(
        string $name,
        public string $description = '',
        public string $group = '',
    ) {
        if ($name === '') {
            throw new LogicException(lang('Commands.emptyCommandName'));
        }

        if (preg_match('/^[^\s\:]++(\:[^\s\:]++)*$/', $name) !== 1) {
            throw new LogicException(lang('Commands.invalidCommandName', [$name]));
        }

        $this->name = $name;
    }
}

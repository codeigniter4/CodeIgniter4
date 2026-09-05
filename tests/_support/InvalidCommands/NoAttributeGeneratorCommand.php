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

namespace Tests\Support\InvalidCommands;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(name: 'make:noattribute', description: 'Fixture generator command missing the GeneratorCommand attribute.', group: 'Fixtures')]
final class NoAttributeGeneratorCommand extends AbstractGeneratorCommand
{
}

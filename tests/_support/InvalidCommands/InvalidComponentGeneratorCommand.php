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
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:invalidcomponent', description: 'Fixture generator command with an invalid component.', group: 'Fixtures')]
#[GeneratorCommand(component: '(', template: 'config.tpl.php')]
final class InvalidComponentGeneratorCommand extends AbstractGeneratorCommand
{
}

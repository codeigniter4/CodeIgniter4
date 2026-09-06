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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:entity', description: 'Generates a new entity file.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Entity',
    template: 'entity.tpl.php',
    directory: 'Entities',
    classNameLang: 'CLI.generator.className.entity',
)]
class EntityGenerator extends AbstractGeneratorCommand
{
}

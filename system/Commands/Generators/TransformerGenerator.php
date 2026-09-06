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

#[Command(name: 'make:transformer', description: 'Generates a new transformer file.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Transformer',
    template: 'transformer.tpl.php',
    directory: 'Transformers',
    classNameLang: 'CLI.generator.className.transformer',
)]
class TransformerGenerator extends AbstractGeneratorCommand
{
}

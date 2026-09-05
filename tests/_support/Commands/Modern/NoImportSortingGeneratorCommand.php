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

namespace Tests\Support\Commands\Modern;

use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:unsortedwidget', description: 'Fixture generator command that keeps its imports unsorted.', group: 'Fixtures')]
#[GeneratorCommand(component: 'Widget', template: 'config.tpl.php', directory: 'Widgets', namespace: 'App', sortImports: false)]
final class NoImportSortingGeneratorCommand extends ImportSortingGeneratorCommand
{
}

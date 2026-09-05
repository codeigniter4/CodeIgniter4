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

use CodeIgniter\CLI\AbstractGeneratorCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Attributes\GeneratorCommand;

#[Command(name: 'make:trimmedwidget', description: 'Fixture generator command with trimmed options and forced suffixing.', group: 'Fixtures')]
#[GeneratorCommand(component: 'Widget', template: 'config.tpl.php', directory: 'Widgets', classNameLang: 'CLI.generator.className.config')]
final class TrimmedOptionsGeneratorCommand extends AbstractGeneratorCommand
{
    protected function provideGeneratorOptions(): void
    {
        $this->addNamespaceOption();
    }

    protected function shouldAppendSuffix(): bool
    {
        return true;
    }

    protected function getReplacements(string $class): array
    {
        return ['{namespace}' => 'App\Widgets\Custom'];
    }
}

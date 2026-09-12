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
use Config\Generators;

#[Command(name: 'make:cell', description: 'Generates a new Controlled Cell file and its view.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Cell',
    template: 'cell.tpl.php',
    directory: 'Cells',
    classNameLang: 'CLI.generator.className.cell',
)]
class CellGenerator extends AbstractGeneratorCommand
{
    protected function provideGeneratorOptions(): void
    {
        $this->addNamespaceOption()->addForceOption();
    }

    protected function shouldAppendSuffix(): bool
    {
        return true;
    }

    protected function execute(array $arguments, array $options): int
    {
        $views = config(Generators::class)->views[$this->getName()] ?? [];

        $this->templatePath = $views['class'] ?? null;

        $classExitCode = $this->generateClass();

        $this->templatePath = $views['view'] ?? null;
        $this->template     = 'cell_view.tpl.php';

        $viewExitCode = $this->generateView($this->getViewName($this->qualifyClassName()));

        return $classExitCode | $viewExitCode;
    }

    /**
     * Derives the namespaced view name from the qualified cell class, dropping the `Cell` suffix.
     */
    private function getViewName(string $class): string
    {
        $segments = explode('\\', $class);
        $basename = decamelize(array_pop($segments));

        $segments[] = preg_replace('/_cell$/', '', $basename) ?? $basename;

        return implode('\\', $segments);
    }
}

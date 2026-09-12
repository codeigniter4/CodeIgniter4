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

#[Command(name: 'make:test', description: 'Generates a new test file.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Test',
    template: 'test.tpl.php',
    classNameLang: 'CLI.generator.className.test',
)]
class TestGenerator extends AbstractGeneratorCommand
{
    private const DEFAULT_NAMESPACE = 'Tests';

    protected function provideGeneratorOptions(): void
    {
        $this->addNamespaceOption(self::DEFAULT_NAMESPACE)->addForceOption();
    }

    protected function initialize(array &$arguments, array &$options): void
    {
        $autoloader = service('autoloader');
        $autoloader->addNamespace('CodeIgniter', TESTPATH . 'system');
        $autoloader->addNamespace(self::DEFAULT_NAMESPACE, ROOTPATH . 'tests');
    }

    protected function shouldAppendSuffix(): bool
    {
        return true;
    }

    protected function getNamespace(): string
    {
        if ($this->hasUnboundOption('namespace')) {
            return parent::getNamespace();
        }

        helper('inflector');

        $name       = $this->getValidatedArgument('name');
        $segments   = array_map(pascalize(...), explode('\\', str_replace('/', '\\', $name)));
        $autoloader = service('autoloader');

        while ($segments !== []) {
            array_pop($segments);

            $namespace = implode('\\', $segments);

            if ($namespace !== '' && $autoloader->getNamespace($namespace) !== []) {
                return $namespace;
            }
        }

        return self::DEFAULT_NAMESPACE;
    }

    protected function getBasePath(string $namespace): ?string
    {
        foreach (service('autoloader')->getNamespace($namespace) as $candidate) {
            if (str_contains($candidate, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
                return $candidate;
            }
        }

        return null;
    }
}

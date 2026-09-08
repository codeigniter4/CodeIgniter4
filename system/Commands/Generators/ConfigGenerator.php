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

#[Command(name: 'make:config', description: 'Generates a new config file.', group: 'Generators')]
#[GeneratorCommand(
    component: 'Config',
    template: 'config.tpl.php',
    directory: 'Config',
    classNameLang: 'CLI.generator.className.config',
)]
class ConfigGenerator extends AbstractGeneratorCommand
{
    protected function getReplacements(string $class): array
    {
        $segments = explode('\\', $class);
        array_pop($segments);

        $namespace = implode('\\', $segments);
        $prefix    = APP_NAMESPACE . '\\';

        if (! str_starts_with($namespace, $prefix)) {
            return [];
        }

        return ['{namespace}' => substr($namespace, strlen($prefix))];
    }
}
